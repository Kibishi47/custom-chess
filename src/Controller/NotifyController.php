<?php

namespace App\Controller;

use App\Dto\MessageDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

final class NotifyController extends AbstractController
{
    #[Route('/api/notify', name: 'api_notify', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MessageDto $messageDto,
        HubInterface $hub,
    ): JsonResponse
    {
        $update = new Update(
            topics: 'https://example.com/notify',
            data: json_encode([
                'message' => $messageDto->message,
            ])
        );

        $hub->publish($update);

        return $this->json([
            'success' => true,
            'data' => [
                'message' => $messageDto->message,
            ]
        ]);
    }
}
