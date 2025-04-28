<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

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