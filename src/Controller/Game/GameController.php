<?php

namespace App\Controller\Game;

use App\Chess\Board\BoardType;
use App\Dto\JoinGameDto;
use App\Entity\Game;
use App\Entity\GamePlayer;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Service\Mercure\MercurePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class GameController extends AbstractController
{
    public function __construct(
        private GameRepository $gameRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private MercurePublisher $publisher,
    ) {}

    #[Route('/api/game/types', methods: ['GET'], format: 'json')]
    public function types(): JsonResponse
    {
        $types = [];

        foreach (BoardType::cases() as $type) {
            $types[$type->value] = $type->getLabel();
        }

        return $this->json($types);
    }

    #[Route('/api/game/join', methods: ['POST'], format: 'json')]
    public function join(
        #[MapRequestPayload] JoinGameDto $dto,
        #[CurrentUser] User $user
    ): JsonResponse {
        if ($activeGame = $this->gameRepository->findActiveGameForPlayer($user)) {
            return new JsonResponse($this->serializeGame($activeGame), json: true);
        }

        if (!$game = $this->gameRepository->findAvailableGame($dto->getBoardType())) {
            $game = new Game();
            $game->setBoardType($dto->getBoardType()->value);
        }

        $color = $game->getRandomColor();
        $player = (new GamePlayer())
            ->setColor($color)
            ->setPlayer($user)
            ->setGame($game);

        $game->addGamePlayer($player);
        $game->start();

        $this->entityManager->persist($player);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        $this->publishGame($game);

        return new JsonResponse($this->serializeGame($game), json: true);
    }

    #[Route('/api/game/quit', methods: ['POST'])]
    public function quit(#[CurrentUser] User $user): JsonResponse
    {
        if ($active = $this->gameRepository->findActiveGameForPlayer($user)) {
            $active->cancel();
            $this->entityManager->flush();
            $this->publishGame($active);
        }

        return new JsonResponse();
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
