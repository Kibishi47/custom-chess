<?php

namespace App\Chess\Piece;

use App\Chess\Board\Position;
use App\Chess\Board\Square;
use function Symfony\Component\String\u;

abstract class Piece
{
    public bool $canBeChecked = false;

    public function __construct(
        public Square $square,
        public string $color // white | black
    ) {}

    public function getKey(): string
    {
        return u((new \ReflectionClass($this))->getShortName())->snake()->toString();
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

    public static function getFromType(string $type, Square $square, string $color): ?Piece
    {
        $className = __NAMESPACE__ . '\\Type\\' . u($type)->camel()->toString();
        return class_exists($className) ? new $className($square, $color) : null;
    }

    protected function nothingBlocking(Square $target): bool
    {
        $start = $this->getPosition();
        $end = $target->position;

        $dx = $end->x - $start->x;
        $dy = $end->y - $start->y;

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
}
