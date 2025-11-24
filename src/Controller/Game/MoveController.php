<?php

namespace App\Controller\Game;

use App\Dto\MoveDto;
use App\Entity\Game;
use App\Entity\Move;
use App\Chess\Engine\GameEngine;
use App\Service\Game\GameService;
use App\Service\Mercure\MercurePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MoveController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MercurePublisher $publisher,
        private GameService $gameService,
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

        $this->gameService->setData($game);
        if (!$game->hasLegalMoves()) {
            $game->finish();
        }

        $this->entityManager->persist($move);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $serializedGame = $this->gameService->serializeGame($game, [
            'groups' => ['game.info']
        ]);

        $this->publisher->publish("/api/game/{$game->getId()}", $serializedGame);

        return new JsonResponse($serializedGame, json: true);
    }
}
