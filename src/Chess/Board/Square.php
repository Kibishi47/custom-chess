<?php

namespace App\Chess\Board;

use App\Chess\Piece\Piece;

class Square
{
    public function __construct(
        public ?Board $board,
        public Position $position,
        public ?Piece $piece = null,
        public bool $enPassant = false,
    ) {}

    public function getPositionName(): string
    {
        return $this->position->getPositionName();
    }
}
