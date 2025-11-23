<?php

namespace App\Controller\Game;

use App\Chess\Board\BoardType;
use App\Dto\JoinGameDto;
use App\Entity\Game;
use App\Entity\GamePlayer;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Service\Mercure\MercurePublisher;
use App\Service\Security\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

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
        $types = array_reduce(BoardType::cases(), function (array|null $carry, BoardType $boardType) {
            $carry[$boardType->value] = $boardType->getLabel();
            return $carry;
        });

        return $this->json($types);
    }

    #[Route('/api/game/join', methods: ['POST'], format: 'json')]
    public function join(#[MapRequestPayload] JoinGameDto $dto, #[CurrentUser] User $user): JsonResponse
    {
        if ($activeGame = $this->gameRepository->findActiveGameForPlayer($user)) {
            return new JsonResponse($this->serializeGame($activeGame), json: true);
        }

        if (!$game = $this->gameRepository->findAvailableGame($dto->getBoardType())) {
            $game = new Game();
            $game->setBoardType($dto->getBoardType()->getClass());
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

        $this->publishGame($game);

        return new JsonResponse($this->serializeGame($game), json: true);
    }

    #[Route('/api/game/quit', methods: ['POST'])]
    public function quit(Request $request, UserService $userService): JsonResponse
    {
        try {
            $user = $userService->getUserFromBodyRequest($request);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], $e->getCode());
        }

        if ($activeGame = $this->gameRepository->findActiveGameForPlayer($user)) {
            $activeGame->cancel();

            $this->entityManager->persist($activeGame);
            $this->entityManager->flush();

            $this->publishGame($activeGame);
        }

        return new JsonResponse();
    }

    private function publishGame(Game $game): void
    {
        $gameSerialized = $this->serializeGame($game);
        $this->publisher->publish("/api/game/{$game->getId()}", $gameSerialized);
    }

    private function serializeGame(Game $game): string
    {
        return $this->serializer->serialize($game, 'json', [
            'groups' => ['game.info'],
        ]);
    }
}
