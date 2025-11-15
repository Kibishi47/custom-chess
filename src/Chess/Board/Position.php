<?php

namespace App\Chess\Board;

class Position
{
    public function __construct(
        public int $x,
        public int $y
    ) {}

    public function delta(Position $target): array
    {
        return [
            abs($target->x - $this->x),
            abs($target->y - $this->y),
        ];
    }

    public function signedDelta(Position $target): array
    {
        return [
            $target->x - $this->x,
            $target->y - $this->y,
        ];
    }

    public function toSquare(): string
    {
        return chr($this->x + ord('a')) . ($this->y + 1);
    }
}
