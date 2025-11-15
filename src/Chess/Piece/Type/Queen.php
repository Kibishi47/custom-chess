<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Queen extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        $start = $this->getPosition();
        $end = $endSquare->position;

        $dx = abs($start->x - $end->x);
        $dy = abs($start->y - $end->y);

        // Dame = tour OU fou
        $isRookMove = ($start->x === $end->x || $start->y === $end->y);
        $isBishopMove = ($dx === $dy);

        if (!$isRookMove && !$isBishopMove) {
            return false;
        }

        return $this->nothingBlocking($endSquare);
    }
}
