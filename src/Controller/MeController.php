<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class MeController extends AbstractController
{
  public function __invoke(Security $security): JsonResponse
  {
    if(!$security->getUser()) {
      return new JsonResponse(['message' => 'User non trouvé !'], Response::HTTP_UNAUTHORIZED);
    }
    return $this->json($security->getUser(), 200, [], ['groups' => 'user:read']);
  }
}


{
//   public function __invoke(Security $security, SerializerInterface $serializer): JsonResponse
//   {
//     // Accès à toutes les informations du user
//       $user = $security->getUser();

//     // Si il n'y a pas de user alors retourne message d'erreur
//       if (!$user) {
//           return new JsonResponse(['message' => 'Utilisateur non trouvé !'], Response::HTTP_UNAUTHORIZED);
//       }

//       // Accéder aux réservations de l'utilisateur
//       $bookings = $user->getBookings();

//       // Accéder aux stations de recharge que l'utilisateur loue
//       $ownedChargingStations = $user->getChargingStations();

//       // Inclure les réservations et les stations de recharge (avec leurs locations et timeslots)
//       $userData = [
//           'user' => $user,
//           'bookings' => $bookings,
//           'ownedChargingStations' => $ownedChargingStations,
//       ];

//       // Sérialiser les données en JSON avec les groupes appropriés
//       $jsonData = $serializer->serialize($userData, 'json', [
//           'groups' => [
//               'user:read',
//               'booking:read',
//               'charging_station:read',
//               'location_station:read', 
//               'timeslot:read',         
//           ],
//       ]);

//       return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
//   }
}