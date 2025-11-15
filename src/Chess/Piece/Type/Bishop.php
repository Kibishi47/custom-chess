<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Bishop extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        [$dx, $dy] = $this->getPosition()->delta($endSquare->position);

        // Fou = diagonale uniquement
        if ($dx !== $dy) {
            return false;
        }

        return $this->nothingBlocking($endSquare);
    }
}
