<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Knight extends Piece
{
    public bool $canJump = true;

    protected function isLegalMove(Square $endSquare): bool
    {
        [$dx, $dy] = $this->getPosition()->delta($endSquare->position);

        return ($dx === 2 && $dy === 1) || ($dx === 1 && $dy === 2);
    }
}
