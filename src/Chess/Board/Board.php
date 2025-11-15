<?php

namespace App\Chess\Board;

use App\Chess\Piece\Piece;

abstract class Board
{
    protected int $width = 8;
    protected int $height = 8;

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
}
