<?php

namespace App\Chess\Board;

use App\Chess\Engine\GameEngine;
use App\Chess\Piece\Piece;
use App\Entity\Game;
use App\Entity\Move;

abstract class Board
{
    public int $width = 8;
    public int $height = 8;

    /** @var Square[][] */
    public array $squares = [];

    public function __construct()
    {
        $this->initializeSquares();
        $this->setup();
    }

    protected function initializeSquares(): void
    {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $this->squares[$x][$y] = new Square(
                    $this,
                    new Position($x, $y),
                    null,
                    false
                );
            }
        }
    }

    abstract protected function setup(): void;

    public function getSquare(int $x, int $y): ?Square
    {
        return $this->squares[$x][$y] ?? null;
    }

    public function getSquareFromNotation(string $notation): ?Square
    {
        $pos = $this->squareToPos($notation);
        return $this->getSquare($pos['x'], $pos['y']);
    }

    public function squareToPos(string $square): array
    {
        $file = ord($square[0]) - ord('a');
        $rank = intval(substr($square, 1)) - 1;

        return ['x' => $file, 'y' => $rank];
    }

    public function placePiece(Piece $piece): void
    {
        $sq = $piece->square;
        $this->squares[$sq->position->x][$sq->position->y]->piece = $piece;
    }

    /**
     * @return Piece[]
     */
    public function getPieces(): array
    {
        $pieces = [];

        foreach ($this->squares as $col) {
            foreach ($col as $sq) {
                if ($sq->piece) {
                    $pieces[] = $sq->piece;
                }
            }
        }

        return $pieces;
    }

    public static function createFromGame(Game $game): Board
    {
        $class = BoardType::from($game->getBoardType())->getClass();
        $board = new $class();

        foreach ($game->getMoves() as $move) {
            $engine = new GameEngine();
            $engine->applyMoveOnBoard($board, $move);
        }

        return $board;
    }

    public function generateMovesForColor(string $color): array
    {
        $moves = [];

        foreach ($this->getPieces() as $piece) {
            if ($piece->color !== $color) {
                continue;
            }

            $from = $piece->getPosition()->toSquare();
            $moves[$from] = [];

            for ($x = 0; $x < $this->width; $x++) {
                for ($y = 0; $y < $this->height; $y++) {
                    $target = $this->squares[$x][$y];

                    if ($piece->canMoveTo($target)) {
                        $moves[$from][] = $target->position->toSquare();
                    }
                }
            }

            if (empty($moves[$from])) {
                unset($moves[$from]);
            }
        }

        return $moves;
    }

    public function placePieceByRow(string $pieceClass, int $row, string $color): void
    {
        for ($x = 0; $x < $this->width; $x++) {
            $square = $this->getSquare($x, $row);
            $piece = new $pieceClass($square, $color);
            $square->piece = $piece;
        }
    }
}
