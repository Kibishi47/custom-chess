<?php

namespace App\Controller\Game;

use App\Dto\MoveDto;
use App\Entity\Game;
use App\Entity\Move;
use App\Service\Mercure\MercurePublisher;
use App\Validator\MoveIsLegal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoveController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private MercurePublisher $publisher,
    ) {}

    #[Route('/api/{game}/moves', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MoveDto $dto,
        Game $game,
    ): JsonResponse
    {
        $errors = $this->validator->validate(
            $dto,
            [new MoveIsLegal(board: $game->getBoard())]
        );

        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $move = (new Move())
            ->setMoveNumber(0)
            ->setFromSq($dto->getFromSq())
            ->setToSq($dto->getToSq())
            ->setColor($dto->getColor())
            ->setPiece($dto->getPiece())
        ;

        $game->addMove($move);

        $this->entityManager->persist($move);
        $this->entityManager->flush();

        $game->applyMove($move);

        $this->publishGame($game);

        return new JsonResponse($this->serializeGame($game), json: true);
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
