<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class WhoAmiController extends AbstractController
{
    #[Route('/api/whoami', name: 'whoami', methods: ['GET'])]
    public function __invoke(SerializerInterface $serializer): ?JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Invalid user type'], 401);
        }

        return new JsonResponse($serializer->serialize([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles()
        ], 'json'), json: true);
    }
}
