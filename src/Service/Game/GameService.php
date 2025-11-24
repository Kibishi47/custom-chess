<?php

namespace App\Service\Game;

use App\Chess\Engine\GameEngine;
use App\Entity\Game;
use Symfony\Component\Serializer\SerializerInterface;

class GameService
{
    public function __construct(
        private SerializerInterface $serializer,
        private GameEngine $engine
    ) {}

    public function setData(Game $game): void
    {
        if ($game->dataSetted) return;

        $this->generateLegalMoves($game);
        $this->generateCheck($game);
        $game->dataSetted = true;
    }

    public function serializeGame(Game $game, array $context = []): string
    {
        if (!$game->dataSetted) {
            $this->setData($game);
        }

        return $this->serializer->serialize($game, 'json', $context);
    }

    private function generateLegalMoves(Game $game): void
    {
        $game->legalMoves = $this->engine->generateLegalMoves($game, $game->getTurnColor());
    }

    private function generateCheck(Game $game): void
    {
        foreach (['white', 'black'] as $color) {
            $game->check[$color] = $this->engine->isCheck($game, $color);
        }
    }
}
