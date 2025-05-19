<?php

namespace App\Controller;

use App\Repository\ChargingStationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class GetChargingStationByUserController extends AbstractController
{
  public function __invoke(Security $security, ChargingStationRepository $chargingStationRepository): JsonResponse
  {
    if(!$security->getUser()) {
      return new JsonResponse(['message' => 'User non trouvé !'], Response::HTTP_UNAUTHORIZED);
    }
    $chargingStation = $chargingStationRepository->findByUser($security->getUser());
    
    return $this->json( $chargingStation, context: ['groups' => 'charging_station:read']);
  }
}


