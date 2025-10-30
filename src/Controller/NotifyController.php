<?php

namespace App\Controller;

use App\Dto\MessageDto;
use App\Service\MercurePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class NotifyController extends AbstractController
{
    #[Route('/api/notify', name: 'api_notify', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MessageDto $messageDto,
        MercurePublisher $publisher,
    ): JsonResponse
    {
        $publisher->publish(
            path: '/api/notify',
            data: [
                'message' => $messageDto->message,
            ]
        );

        return $this->json([
            'success' => true,
            'data' => [
                'message' => $messageDto->message,
            ]
        ]);
    }
}
