<?php

namespace App\Controller\Game;

use App\Dto\MoveDto;
use App\Entity\Game;
use App\Entity\Move;
use App\Validator\MoveIsLegal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoveController extends AbstractController
{
    #[Route('/api/{game}/moves', methods: ['POST'], format: 'json')]
    public function __invoke(
        #[MapRequestPayload] MoveDto $dto,
        Game $game,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        $errors = $validator->validate(
            $dto,
            [new MoveIsLegal(board: $game->getBoard())]
        );

        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $move = (new Move())
            ->setGame($game)
            ->setMoveNumber(0)
            ->setFromSq($dto->getFromSq())
            ->setToSq($dto->getToSq())
            ->setColor($dto->getColor())
            ->setPiece($dto->getPiece())
        ;

        $entityManager->persist($move);
        $entityManager->flush();

        $game->applyMove($move);

        return $this->json($game, context: ['groups' => ['game.info']]);
    }
}
