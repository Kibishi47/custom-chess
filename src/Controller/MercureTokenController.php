<?php

declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MercureTokenController extends AbstractController
{
    #[Route('/mercure-token', name: 'mercure_token')]
    public function __invoke(Request $request): JsonResponse
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $payload = [
            'mercure' => [
                'subscribe' => [
                    "{$baseUrl}/api/notify/private"
                ]
            ]
        ];

        $token = JWT::encode($payload, getenv('MERCURE_JWT_SECRET'), 'HS256');

        return $this->json([
            'token' => $token,
        ]);
    }
}
