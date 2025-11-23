<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Pawn extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        [$dxSigned, $dySigned] = $this->getPosition()->signedDelta($endSquare->position);
        [$dxAbs,   $dyAbs]     = $this->getPosition()->delta($endSquare->position);

        $direction = $this->color === 'white' ? 1 : -1;

        if ($dxAbs === 1 && $dySigned === $direction) {
            return $endSquare->enPassant
                || ($endSquare->piece && $endSquare->piece->color !== $this->color);
        }

        return ($this->isAtStartingSquare() && $dxAbs === 0 && $dySigned === 2 * $direction)
            || ($dxAbs === 0 && $dySigned === $direction);
    }
}
