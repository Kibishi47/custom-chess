<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Knight extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        [$dx, $dy] = $this->getPosition()->delta($endSquare->position);

        // Cavalier = L
        return ($dx === 1 && $dy === 2)
            || ($dx === 2 && $dy === 1);
    }
}
