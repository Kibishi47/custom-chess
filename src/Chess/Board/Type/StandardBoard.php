<?php

namespace App\Chess\Board\Type;

use App\Chess\Board\Board;
use App\Chess\Piece\Type\Pawn;
use App\Chess\Piece\Type\Rook;
use App\Chess\Piece\Type\Knight;
use App\Chess\Piece\Type\Bishop;
use App\Chess\Piece\Type\Queen;
use App\Chess\Piece\Type\King;

class StandardBoard extends Board
{
    protected function setup(): void
    {
        // White back rank
        $this->placePiece(new Rook($this->getSquare(0,0), 'white'));
        $this->placePiece(new Knight($this->getSquare(1,0), 'white'));
        $this->placePiece(new Bishop($this->getSquare(2,0), 'white'));
        $this->placePiece(new Queen($this->getSquare(3,0), 'white'));
        $this->placePiece(new King($this->getSquare(4,0), 'white'));
        $this->placePiece(new Bishop($this->getSquare(5,0), 'white'));
        $this->placePiece(new Knight($this->getSquare(6,0), 'white'));
        $this->placePiece(new Rook($this->getSquare(7,0), 'white'));

        // White pawns
        $this->placePieceByRow(Pawn::class, 1, 'white');

        // Black back rank
        $this->placePiece(new Rook($this->getSquare(0,7), 'black'));
        $this->placePiece(new Knight($this->getSquare(1,7), 'black'));
        $this->placePiece(new Bishop($this->getSquare(2,7), 'black'));
        $this->placePiece(new Queen($this->getSquare(3,7), 'black'));
        $this->placePiece(new King($this->getSquare(4,7), 'black'));
        $this->placePiece(new Bishop($this->getSquare(5,7), 'black'));
        $this->placePiece(new Knight($this->getSquare(6,7), 'black'));
        $this->placePiece(new Rook($this->getSquare(7,7), 'black'));

        // Black pawns
        $this->placePieceByRow(Pawn::class, 6, 'black');
    }
}
