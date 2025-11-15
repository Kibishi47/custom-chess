<?php

namespace App\Chess\Board;

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
                    false,
                );
            }
        }
    }

    abstract protected function setup(): void;

    public function inBounds(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->width
            && $y >= 0 && $y < $this->height;
    }

    public function getSquare(int $x, int $y): ?Square
    {
        return $this->squares[$x][$y] ?? null;
    }

    public function placePiece(Piece $piece): void
    {
        $pos = $piece->getPosition();
        $square = $this->getSquare($pos->x, $pos->y);
        $square->piece = $piece;
        $piece->square = $square;
    }

    public function placePieceByRow(string $pieceClass, int $row, string $color): void
    {
        for ($i = 0; $i < $this->width; $i++) {
            $this->placePiece(new $pieceClass($this->getSquare($i, $row), $color));
        }
    }

    /** @return Piece[] */
    public function getPieces(): array
    {
        $pieces = [];
        foreach ($this->squares as $col) {
            foreach ($col as $square) {
                if ($square->piece) {
                    $pieces[] = $square->piece;
                }
            }
        }
        return $pieces;
    }

    public function applyMove(Move $move): void
    {
        $from = $this->squareToPos($move->getFromSq());
        $to   = $this->squareToPos($move->getToSq());

        $fromSq = $this->getSquare($from['x'], $from['y']);
        $toSq   = $this->getSquare($to['x'], $to['y']);

        if (!$fromSq->piece) {
            throw new \LogicException("Invalid move history: no piece at {$move->getFromSq()}");
        }

        $piece = $fromSq->piece;

        // Move
        $toSq->piece = $piece;
        $fromSq->piece = null;

        // Update current square
        $piece->square = $toSq;
    }

    private function squareToPos(string $square): array
    {
        $file = ord($square[0]) - ord('a');
        $rank = intval(substr($square, 1)) - 1;

        return ['x' => $file, 'y' => $rank];
    }

    public static function createFromGame(Game $game): Board
    {
        $boardTypeClass = $game->getBoardType();
        /** @var Board $board */
        $board = new $boardTypeClass();

        foreach ($game->getMoves() as $move) {
            $board->applyMove($move);
        }

        return $board;
    }

    public function generateMovesForColor(string $color): array
    {
        $moves = [];

        foreach ($this->getPieces() as $piece) {

            // Only specific color
            if ($piece->color !== $color) {
                continue;
            }

            $fromSquare = $piece->getPosition()->toSquare();
            $moves[$fromSquare] = [];

            for ($x = 0; $x < $this->width; $x++) {
                for ($y = 0; $y < $this->height; $y++) {

                    /** @var Square $targetSquare */
                    $targetSquare = $this->getSquare($x, $y);

                    if ($piece->canMoveTo($targetSquare)) {
                        $moves[$fromSquare][] = $targetSquare->position->toSquare();
                    }
                }
            }

            // Remove unnecessary key
            if (empty($moves[$fromSquare])) {
                unset($moves[$fromSquare]);
            }
        }

        return $moves;
    }
}
