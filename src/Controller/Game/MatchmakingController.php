<?php

namespace App\Controller\Game;

use App\Entity\Game;
use App\Entity\GamePlayer;
use App\Entity\User;
use App\Repository\GamePlayerRepository;
use App\Repository\GameRepository;
use App\Service\Mercure\MercurePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

class MatchmakingController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly MercurePublisher $publisher,
    ) {}

    #[Route('/api/matchmaking', methods: ['POST'], format: 'json')]
    public function __invoke(#[CurrentUser] User $user): JsonResponse
    {
        if ($activeGame = $this->gameRepository->findActiveGameForPlayer($user)) {
            return new JsonResponse($this->serializeGame($activeGame), json: true);
        }

        if (!$game = $this->gameRepository->findAvailableGame()) {
            $game = new Game();
        }

        $color = $game->getRandomColor();
        $gamePlayer = (new GamePlayer())
            ->setColor($color)
            ->setGame($game)
            ->setPlayer($user);

        $game->addGamePlayer($gamePlayer);
        $game->start();

        $this->entityManager->persist($gamePlayer);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $gameSerialized = $this->serializeGame($game);
        $this->publisher->publish('/api/matchmaking', $gameSerialized);

        return new JsonResponse($gameSerialized, json: true);
    }

    private function serializeGame(Game $game): string
    {
        return $this->serializer->serialize($game, 'json', [
            'groups' => ['matchmaking'],
        ]);
    }
}
