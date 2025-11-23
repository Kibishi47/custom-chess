<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class King extends Piece
{
    public bool $canBeChecked = true;

    protected function isLegalMove(Square $endSquare): bool
    {
        [$dx, $dy] = $this->getPosition()->delta($endSquare->position);

        if ($dx <= 1 && $dy <= 1) {
            return true;
        }

        if ($dy === 0 && $dx === 2 && !$this->hasMoved) {
            return true;
        }

        return false;
    }
}
