<?php

namespace App\Controller\Game;

use App\Entity\Game;
use App\Entity\GamePlayer;
use App\Entity\User;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class MatchmakingController extends AbstractController
{
    public function __construct(
        private GameRepository $gameRepository,
        private EntityManagerInterface $entityManager,
    ) {}
    #[Route('/api/matchmaking', methods: ['POST'], format: 'json')]
    public function __invoke(#[CurrentUser] User $user): JsonResponse
    {
        $returnContext = ['groups' => ['matchmaking']];

        if ($activeGame = $this->gameRepository->findActiveGameForPlayer($user)) {
            return $this->json($activeGame, context: $returnContext);
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

        return $this->json($game, context: $returnContext);
    }
}
