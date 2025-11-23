<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Pawn extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        [$dxSigned, $dySigned] = $this->getPosition()->signedDelta($endSquare->position);
        [$dxAbs, $dyAbs] = $this->getPosition()->delta($endSquare->position);

        $direction = $this->color === 'white' ? 1 : -1;

        // Capture diagonale
        if ($dxAbs === 1 && $dySigned === $direction) {
            return $endSquare->enPassant
                || ($endSquare->piece && $endSquare->piece->color !== $this->color);
        }

        // Avance simple
        if ($dxAbs === 0 && $dySigned === $direction) {
            return $endSquare->piece === null; // pas de pièce devant
        }

        if ($this->isAtStartingSquare() && $dxAbs === 0 && $dySigned === $direction * 2) {
            return $this->nothingBlocking($endSquare);
        }

        return false;
    }
}
