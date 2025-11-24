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

        // Pièce à manger
        if ($dxAbs === 1 && $dySigned === $direction) {
            return $endSquare->enPassant
                || ($endSquare->piece && $endSquare->piece->color !== $this->color);
        }

        // Avancer
        if ($dxAbs === 0 && $dySigned === $direction) {
            return $endSquare->piece === null;
        }

        // Déplacement de 2 case au niveau du départ
        return $this->isAtStartingSquare() && $dxAbs === 0 && $dySigned === 2 * $direction;
    }
}
