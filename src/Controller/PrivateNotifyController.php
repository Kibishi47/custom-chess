<?php

namespace App\Controller;

use App\Dto\MessageDto;
use App\Service\MercurePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class PrivateNotifyController extends AbstractController
{
    #[Route('/api/notify/private', name: 'api_notify_private', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MessageDto $messageDto,
        MercurePublisher $publisher,
    ): JsonResponse
    {
        $publisher->publish(
            path: '/api/notify/private',
            data: [
                'message' => $messageDto->message,
            ],
            private: true
        );

        return $this->json([
            'success' => true,
            'data' => [
                'message' => $messageDto->message,
            ],
        ]);
    }
}
