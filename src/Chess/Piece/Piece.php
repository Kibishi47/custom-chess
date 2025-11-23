<?php

namespace App\Chess\Piece;

use App\Chess\Board\Position;
use App\Chess\Board\Square;
use function Symfony\Component\String\u;

abstract class Piece
{
    public bool $canBeChecked = false;
    public Square $startingSquare;

    public function __construct(
        public Square $square,
        public string $color // white | black
    ) {
        $this->startingSquare = clone $square;
    }

    public function getKey(): string
    {
        return u((new \ReflectionClass($this))->getShortName())->snake()->toString();
    }

    public function isAtStartingSquare(): bool
    {
        return $this->startingSquare->position->x === $this->square->position->x
            && $this->startingSquare->position->y === $this->square->position->y;
    }

    public function getPosition(): Position
    {
        return $this->square->position;
    }

    public function canMoveTo(Square $endSquare, bool $strictColor = true): bool
    {
        // Same position → illegal
        if ($this->getPosition()->x === $endSquare->position->x
            && $this->getPosition()->y === $endSquare->position->y) {
            return false;
        }

        // Cannot capture same color
        if ($endSquare->piece
            && $endSquare->piece->color === $this->color
            && $strictColor) {
            return false;
        }

        return $this->isLegalMove($endSquare);
    }

    abstract protected function isLegalMove(Square $endSquare): bool;

    protected function nothingBlocking(Square $target): bool
    {
        $start = $this->getPosition();
        $end = $target->position;

        [$dx, $dy] = $start->signedDelta($end);

        $stepX = $dx === 0 ? 0 : ($dx > 0 ? 1 : -1);
        $stepY = $dy === 0 ? 0 : ($dy > 0 ? 1 : -1);

        $x = $start->x + $stepX;
        $y = $start->y + $stepY;

        while ($x !== $end->x || $y !== $end->y) {
            if ($target->board->getSquare($x, $y)?->piece) {
                return false;
            }
            $x += $stepX;
            $y += $stepY;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'color' => $this->color,
            'square' => $this->square->toSquare(),
        ];
    }
}
