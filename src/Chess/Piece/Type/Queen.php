<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Queen extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        [$dx, $dy] = $this->getPosition()->delta($endSquare->position);

        return $dx === 0 || $dy === 0 || $dx === $dy;
    }
}
