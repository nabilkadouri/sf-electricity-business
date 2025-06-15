<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me_get', methods: ['GET'])]
    public function __invoke(Security $security): JsonResponse
    {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User non trouvé !'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    /**
     * Gère l'upload de la photo de profil de l'utilisateur connecté.
     * Cette route sera appelée par le frontend pour envoyer l'image.
     */
    #[Route('/api/me/upload-picture', name: 'api_me_upload_picture', methods: ['POST'])]
    public function uploadPicture(
        Request $request,
        Security $security,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem // Injectez le service Filesystem
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Vous devez être connecté pour faire ceci.'], Response::HTTP_UNAUTHORIZED);
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

        $uploadDirectory = $parameterBag->get('upload_directory');

        // Supprime l'ancienne photo si elle existe avant d'en uploader une nouvelle
        if ($user->getPicture()) {
            // Récupère juste le nom du fichier depuis le chemin relatif stocké
            $oldPictureBasename = basename($user->getPicture());
            $oldPicturePath = $uploadDirectory . '/' . $oldPictureBasename;
            
            // Vérifie si l'ancienne image n'est PAS l'image par défaut avant de la supprimer physiquement
            // Adaptez 'default_avatar.png' si le nom de votre fichier par défaut est différent
            if ($oldPictureBasename !== 'default_avatar.png' && $filesystem->exists($oldPicturePath)) {
                try {
                    $filesystem->remove($oldPicturePath);
                } catch (IOExceptionInterface $exception) {
                    // Log l'erreur si la suppression échoue, mais ne bloque pas l'upload de la nouvelle image
                    error_log("Impossible de supprimer l'ancienne photo : " . $exception->getMessage());
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

        // Sauvegarde le chemin relatif dans la base de données
        $user->setPicture('uploads/profile_pictures/' . $newFilename);

        $entityManager->persist($user);
        $entityManager->flush();

        // Retourne l'URL complète pour que le frontend puisse l'afficher directement
        $fullPictureUrl = $request->getUriForPath('/' . $user->getPicture());

        return new JsonResponse([
            'message' => 'Photo de profil mise à jour avec succès !',
            'pictureUrl' => $fullPictureUrl
        ], Response::HTTP_OK);
    }

    /**
     * Gère la suppression de la photo de profil de l'utilisateur connecté.
     */
    #[Route('/api/me/delete-picture', name: 'api_me_delete_picture', methods: ['DELETE'])]
    public function deletePicture(
        Security $security,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem // Injectez le service Filesystem
    ): JsonResponse {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Vous devez être connecté pour faire ceci.'], Response::HTTP_UNAUTHORIZED);
        }

        $picturePath = $user->getPicture(); // Le chemin relatif stocké dans la BDD
        $uploadDirectory = $parameterBag->get('upload_directory');

        if ($picturePath) {
            // Récupère juste le nom du fichier depuis le chemin relatif stocké
            $pictureBasename = basename($picturePath);
            // Définissez le nom de votre fichier d'avatar par défaut ici
            $defaultPictureFilename = 'default_avatar.png'; 

            // Vérifie si l'image à supprimer n'est PAS l'image par défaut
            if ($pictureBasename !== $defaultPictureFilename) {
                // Construit le chemin absolu du fichier sur le système de fichiers
                $filePath = $uploadDirectory . '/' . $pictureBasename;

                // Vérifie si le fichier existe physiquement et le supprime
                if ($filesystem->exists($filePath)) {
                    try {
                        $filesystem->remove($filePath);
                    } catch (IOExceptionInterface $exception) {
                        return new JsonResponse(['message' => 'Erreur lors de la suppression du fichier image : ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
        }

        // Met à jour le champ 'picture' dans la base de données à null
        $user->setPicture(null);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Photo de profil supprimée avec succès !'], Response::HTTP_OK);
    }
}
