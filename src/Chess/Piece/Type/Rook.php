<?php

namespace App\Chess\Piece\Type;

use App\Chess\Board\Square;
use App\Chess\Piece\Piece;

class Rook extends Piece
{
    protected function isLegalMove(Square $endSquare): bool
    {
        // Déplacement en ligne ou colonne uniquement
        $start = $this->getPosition();
        $end = $endSquare->position;

        if ($start->x !== $end->x && $start->y !== $end->y) {
            return false;
        }

        // Vérification : rien sur le chemin
        return $this->nothingBlocking($endSquare);
    }
}
