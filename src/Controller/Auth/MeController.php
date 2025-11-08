<?php

namespace App\Controller\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

final class MeController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'], format: 'json')]
    public function __invoke(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) return new JsonResponse(['message' => 'Unauthorized'], 401);

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
        ]);
    }
}
