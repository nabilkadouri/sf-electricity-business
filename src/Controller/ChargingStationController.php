<?php

namespace App\Controller;

use App\Entity\ChargingStation;
use App\Entity\User;
use App\Entity\LocationStation;
use App\Repository\ChargingStationRepository;
use App\Repository\LocationStationRepository;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Enum\ChargingStationStatus;

#[Route('/api/charging_stations')]
final class ChargingStationController extends AbstractController{
    // #[Route('/charging/station', name: 'app_charging_station')]
    // public function index(): Response
    // {
    //     return $this->render('charging_station/index.html.twig', [
    //         'controller_name' => 'ChargingStationController',
    //     ]);
    // }

    /**
     * Crée une nouvelle borne de recharge et l'associe à l'utilisateur connecté.
     * Gère également la création ou l'association d'une LocationStation.
     */
    #[Route('', name: 'api_charging_stations_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        LocationStationRepository $locationStationRepository,
        ValidatorInterface $validator,
        GeocodingService $geocodingService,
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise pour créer une borne.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        // Validation de base des champs requis du formulaire
        if (empty($data['nameStation']) || empty($data['power']) || empty($data['pricePerHour']) ||
            empty($data['locationStation']['address']) || empty($data['locationStation']['postaleCode']) || empty($data['locationStation']['city'])) {
            return new JsonResponse(['message' => 'Des champs obligatoires sont manquants pour la borne ou la localisation.'], Response::HTTP_BAD_REQUEST);
        }


        // --- GESTION DE LA LOCATION STATION ---
        $locationData = $data['locationStation'];
        // Construction de l'adresse complète pour le géocodage
        $fullAddress = sprintf(
            '%s, %s %s',
            $locationData['address'],
            $locationData['postaleCode'],
            $locationData['city']
        );

        $locationStation = $locationStationRepository->findOneBy([
            'address' => $locationData['address'],
            'postaleCode' => $locationData['postaleCode'],
            'city' => $locationData['city'],
            'name' => $locationData['name'] ?? null
        ]);

        if (!$locationStation) {
            $locationStation = new LocationStation();
            $locationStation->setAddress($locationData['address']);
            $locationStation->setPostaleCode($locationData['postaleCode']);
            $locationStation->setCity($locationData['city']);
            $locationStation->setName($locationData['name'] ?? null);

            // NOUVEAU: Appel au service de géocodage pour obtenir latitude et longitude
            $coordinates = $geocodingService->getCoordinates($fullAddress);
            if ($coordinates) {
                $locationStation->setLatitude($coordinates['latitude']);
                $locationStation->setLongitude($coordinates['longitude']);
            } else {
                // Optionnel: Gérer le cas où le géocodage échoue (ex: retourner une erreur ou un avertissement)
                // Pour l'instant, les champs latitude/longitude resteront null si non trouvés.
                $this->addFlash('warning', 'Impossible de géocoder l\'adresse pour la borne.');
            }
            
            $entityManager->persist($locationStation);
        }
        

        $chargingStation = new ChargingStation();
        $chargingStation->setUser($user);
        $chargingStation->setLocationStation($locationStation);

        // Hydratation de l'entité ChargingStation avec les données du formulaire
        $chargingStation->setNameStation($data['nameStation']);
        $chargingStation->setPower($data['power']);
        $chargingStation->setPricePerHour($data['pricePerHour']);
        
        // Champs optionnels ou avec valeurs par défaut
        $chargingStation->setDescription($data['description'] ?? null);
        $chargingStation->setPlugType($data['plugType'] ?? 'Type 2');
        $chargingStation->setIsAvailable($data['isAvailable'] ?? false);
        
        // Gestion de l'Enum ChargingStationStatus
        if (isset($data['status']) && ChargingStationStatus::tryFrom($data['status'])) {
            $chargingStation->setStatus(ChargingStationStatus::from($data['status']));
        } else {
            $chargingStation->setStatus(ChargingStationStatus::PENDING); // Statut par défaut si non fourni ou invalide
        }


        // --- GESTION DES TIMESLOTS ---
        if (isset($data['timeslots']) && is_array($data['timeslots'])) {
            foreach ($data['timeslots'] as $timeslotData) {
                // Validation basique pour chaque timeslot
                if (empty($timeslotData['dayOfWeek']) || empty($timeslotData['startTime']) || empty($timeslotData['endTime'])) {
                    return new JsonResponse(['message' => 'Les informations complètes sont requises pour chaque Timeslot.'], Response::HTTP_BAD_REQUEST);
                }

                // Vérification de l'Enum DayOfWeek
                if (!DayOfWeek::tryFrom($timeslotData['dayOfWeek'])) {
                     return new JsonResponse(['message' => 'Jour de la semaine invalide pour un Timeslot : ' . $timeslotData['dayOfWeek']], Response::HTTP_BAD_REQUEST);
                }

                $timeslot = new Timeslot();
                $timeslot->setChargingStation($chargingStation);
                $timeslot->setDayOfWeek(DayOfWeek::from($timeslotData['dayOfWeek']));
                try {
                    $timeslot->setStartTime(new \DateTimeImmutable($timeslotData['startTime']));
                    $timeslot->setEndTime(new \DateTimeImmutable($timeslotData['endTime']));
                } catch (\Exception $e) {
                    return new JsonResponse(['message' => 'Format d\'heure invalide pour un Timeslot: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
                }
                
                $chargingStation->addTimeslot($timeslot); 
            }
        }
        

        // Valider l'entité ChargingStation avant persistance
        $errors = $validator->validate($chargingStation);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['message' => 'Erreurs de validation: ' . implode(', ', $errorMessages)], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($chargingStation);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Borne créée avec succès !',
            'id' => $chargingStation->getId()
        ], Response::HTTP_CREATED, [], ['groups' => 'charging_station:read']); // Retourne l'ID et d'autres données
    }


    /**
     * Permet d'uploader une image pour une borne de recharge spécifique.
     */
    #[Route('/{id}/picture', name: 'api_charging_stations_upload_picture', methods: ['POST'])]
    public function uploadPicture(
        #[MapEntity(expr: 'repository.find(id)')]
        ChargingStation $chargingStation,
        Request $request,
        Security $security,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        // TRÈS IMPORTANT : Vérifier que l'utilisateur connecté est bien le propriétaire de la borne
        if ($chargingStation->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès refusé. Cette borne ne vous appartient pas.'], Response::HTTP_FORBIDDEN);
        }

        /** @var UploadedFile|null $pictureFile */
        $pictureFile = $request->files->get('pictureFile');

        if (!$pictureFile) {
            return new JsonResponse(['message' => 'Aucun fichier image n\'a été envoyé.'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($pictureFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['message' => 'Type de fichier non autorisé. Seulement les images (JPEG, PNG, GIF) sont acceptées.'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

        $uploadDirectory = $parameterBag->get('charging_station_upload_directory'); 
        
        // Supprime l'ancienne image de la borne si elle existe et n'est pas l'image par défaut.
        // Cela suppose que 'default_picture_station.png' n'est jamais stockée dans 'uploads/station_pictures'.
        if ($chargingStation->getPicture() && basename($chargingStation->getPicture()) !== 'default_picture_station.png') {
            $oldImageBasename = basename($chargingStation->getPicture());
            $oldImagePath = $uploadDirectory . '/' . $oldImageBasename;
            if ($filesystem->exists($oldImagePath)) {
                try {
                    $filesystem->remove($oldImagePath);
                } catch (IOExceptionInterface $exception) {
                    error_log("Impossible de supprimer l'ancienne photo de borne : " . $exception->getMessage());
                }
            }
        }

        try {
            $pictureFile->move(
                $uploadDirectory,
                $newFilename
            );
        } catch (\FileException $e) {
            return new JsonResponse(['message' => 'Erreur lors du déplacement du fichier image.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $chargingStation->setPicture('uploads/station_pictures/' . $newFilename);

        $entityManager->persist($chargingStation);
        $entityManager->flush();

        $fullPictureUrl = $request->getUriForPath('/' . $chargingStation->getPicture());

        return new JsonResponse([
            'message' => 'Image de borne mise à jour avec succès !',
            'imageUrl' => $fullPictureUrl
        ], Response::HTTP_OK);
    }

    /**
     * Modifie une borne de recharge existante.
     * Les données sont envoyées via une requête PATCH.
     */
    #[Route('/{id}', name: 'api_charging_stations_update', methods: ['PATCH'])]
    public function update(
        #[MapEntity(expr: 'repository.find(id)')]
        ChargingStation $chargingStation,
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LocationStationRepository $locationStationRepository
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($chargingStation->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès refusé. Cette borne ne vous appartient pas.'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        // Mise à jour des champs de la borne si présents dans la requête
        if (isset($data['nameStation'])) {
            $chargingStation->setNameStation($data['nameStation']);
        }
        if (isset($data['description'])) {
            $chargingStation->setDescription($data['description']);
        }
        if (isset($data['power'])) {
            $chargingStation->setPower($data['power']);
        }
        if (isset($data['pricePerHour'])) {
            $chargingStation->setPricePerHour($data['pricePerHour']);
        }
        if (isset($data['plugType'])) {
            $chargingStation->setPlugType($data['plugType']);
        }
        if (isset($data['isAvailable'])) {
            $chargingStation->setIsAvailable($data['isAvailable']);
        }
        if (isset($data['status']) && ChargingStationStatus::tryFrom($data['status'])) {
            $chargingStation->setStatus(ChargingStationStatus::from($data['status']));
        }

        // GESTION DE LA LOCATION STATION LORS DE LA MODIFICATION
        if (isset($data['locationStation'])) {
            $locationData = $data['locationStation'];
            // On s'assure que les champs requis pour une localisation sont présents
            if (isset($locationData['address']) && isset($locationData['postaleCode']) && isset($locationData['city'])) {
                $locationStation = $locationStationRepository->findOneBy([
                    'address' => $locationData['address'],
                    'postaleCode' => $locationData['postaleCode'],
                    'city' => $locationData['city'],
                    'name' => $locationData['name'] ?? null // NOUVEAU: Inclure le nom si fourni
                ]);

                if (!$locationStation) {
                    // Si la localisation n'existe pas, créez-la
                    $locationStation = new LocationStation();
                    $locationStation->setAddress($locationData['address']);
                    $locationStation->setPostaleCode($locationData['postaleCode']);
                    $locationStation->setCity($locationData['city']);
                    $locationStation->setName($locationData['name'] ?? null); 
                    $locationStation->setLatitude($locationData['latitude'] ?? null);
                    $locationStation->setLongitude($locationData['longitude'] ?? null); 
                    $entityManager->persist($locationStation);
                }
                $chargingStation->setLocationStation($locationStation);
            }
        }
       
        // Valider l'entité après mise à jour
        $errors = $validator->validate($chargingStation);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['message' => 'Erreurs de validation: ' . implode(', ', $errorMessages)], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        // Retourne la borne mise à jour avec ses données normalisées
        return $this->json($chargingStation, Response::HTTP_OK, [], ['groups' => 'charging_station:read']);
    }



    /**
     * Supprime une borne de recharge existante.
     * Supprime également le fichier image associé si ce n'est pas l'image par défaut.
     */
    #[Route('/{id}', name: 'api_charging_stations_delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(expr: 'repository.find(id)')]
        ChargingStation $chargingStation,
        Security $security,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($chargingStation->getUser() !== $user) {
            return new JsonResponse(['message' => 'Accès refusé. Cette borne ne vous appartient pas.'], Response::HTTP_FORBIDDEN);
        }

        // Supprime l'image physique associée à la borne, si elle existe et n'est pas l'image par défaut.
        if ($chargingStation->getPicture() && basename($chargingStation->getPicture()) !== 'default_picture_station.png') {
            $imageBasename = basename($chargingStation->getPicture());
            $imagePath = $parameterBag->get('charging_station_upload_directory') . '/' . $imageBasename;
            if ($filesystem->exists($imagePath)) {
                try {
                    $filesystem->remove($imagePath);
                } catch (IOExceptionInterface $exception) {
                    error_log("Impossible de supprimer l'image de la borne : " . $exception->getMessage());
                }
            }
        }

        $entityManager->remove($chargingStation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Borne supprimée avec succès !'], Response::HTTP_NO_CONTENT);
    }

}
