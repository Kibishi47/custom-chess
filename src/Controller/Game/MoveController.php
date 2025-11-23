<?php

namespace App\Controller\Game;

use App\Dto\MoveDto;
use App\Entity\Game;
use App\Entity\Move;
use App\Chess\Engine\GameEngine;
use App\Service\Mercure\MercurePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MoveController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private MercurePublisher $publisher,
        private GameEngine $engine,
    ) {}

    #[Route('/api/{game}/moves', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MoveDto $dto,
        Game $game
    ): JsonResponse {
        $move = (new Move())
            ->setMoveNumber($game->getNextMoveNumber())
            ->setFromSq($dto->getFromSq())
            ->setToSq($dto->getToSq())
            ->setColor($dto->getColor())
            ->setPiece($dto->getPiece());

        $this->engine->applyMove($game, $move);

        $this->entityManager->persist($move);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->publishGame($game);

        return new JsonResponse($this->serializeGame($game), json: true);
    }

    private function publishGame(Game $game): void
    {
        $data = $this->serializeGame($game);
        $this->publisher->publish("/api/game/{$game->getId()}", $data);
    }

    private function serializeGame(Game $game): string
    {
        return $this->serializer->serialize($game, 'json', [
            'groups' => ['game.info']
        ]);
    }
}
