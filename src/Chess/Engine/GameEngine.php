<?php

namespace App\Chess\Engine;

use App\Chess\Piece\Type\Queen;
use App\Entity\Game;
use App\Entity\Move;
use App\Chess\Board\Board;
use App\Chess\Piece\Type\King;
use App\Chess\Piece\Type\Rook;
use App\Chess\Piece\Type\Pawn;

class GameEngine
{
    public function createBoardFromGame(Game $game): Board
    {
        return Board::createFromGame($game);
    }

    public function applyMove(Game $game, Move $move): void
    {
        $board = $game->getBoard();

        $this->applyMoveOnBoard($board, $move);
        $game->addMove($move);
    }

    public function applyMoveOnBoard(Board $board, Move $move): void
    {
        $from = $board->getSquareFromNotation($move->getFromSq());
        $to   = $board->getSquareFromNotation($move->getToSq());
        $piece = $from->piece;

        if (!$piece) {
            throw new \LogicException("No piece at {$move->getFromSq()}");
        }

        // EN PASSANT
        if ($piece instanceof Pawn && $to->piece === null) {
            $this->handleEnPassant($board, $piece, $from, $to);
        }

        // ROQUE
        if ($piece instanceof King
            && abs($to->position->x - $from->position->x) === 2) {
            $this->handleCastling($board, $piece, $from, $to);
        }

        // MOVEMENT
        $to->piece = $piece;
        $from->piece = null;
        $piece->square = $to;
        $piece->markMoved();

        // PROMOTION
        if ($piece instanceof Pawn) {
            $this->handlePromotion($board, $piece);
        }

        $this->clearEnPassant($board);

        if ($piece instanceof Pawn
            && abs($to->position->y - $from->position->y) === 2) {
            $this->markEnPassantSquare($board, $piece, $from, $to);
        }
    }

    protected function handleEnPassant(Board $board, Pawn $pawn, $from, $to): void
    {
        if (!$to->enPassant) {
            return;
        }

        $dir = $pawn->color === 'white' ? -1 : 1;
        $capturedSq = $board->getSquare(
            $to->position->x,
            $to->position->y + $dir
        );

        if ($capturedSq && $capturedSq->piece instanceof Pawn) {
            $capturedSq->piece = null;
        }
    }

    protected function handleCastling(Board $board, King $king, $from, $to): void
    {
        $direction = $to->position->x > $from->position->x ? 1 : -1;
        $rookStartX = $direction === 1 ? 7 : 0;
        $rookEndX = $from->position->x + $direction;

        $rookSq = $board->getSquare($rookStartX, $from->position->y);
        $targetSq = $board->getSquare($rookEndX, $from->position->y);

        if (!$rookSq->piece instanceof Rook) {
            return;
        }

        $rook = $rookSq->piece;

        $rookSq->piece = null;
        $targetSq->piece = $rook;
        $rook->square = $targetSq;
        $rook->markMoved();
    }

    protected function handlePromotion(Board $board, Pawn $pawn): void
    {
        $y = $pawn->getPosition()->y;

        if (($pawn->color === 'white' && $y === 7)
            || ($pawn->color === 'black' && $y === 0)) {

            $queenClass = Queen::class;
            $square = $pawn->square;
            $square->piece = new $queenClass($square, $pawn->color);
        }
    }

    protected function clearEnPassant(Board $board): void
    {
        foreach ($board->squares as $col) {
            foreach ($col as $sq) {
                $sq->enPassant = false;
            }
        }
    }

    protected function markEnPassantSquare(Board $board, Pawn $pawn, $from, $to): void
    {
        $midY = ($from->position->y + $to->position->y) / 2;
        $midSq = $board->getSquare($from->position->x, $midY);

        if ($midSq) {
            $midSq->enPassant = true;
        }
    }

    public function generateLegalMoves(Game $game, string $color): array
    {
        $board = $game->getBoard();

        $pseudo = $board->generateMovesForColor($color);
        $legal = [];

        // Check if after moved the move is legal
        foreach ($pseudo as $from => $targets) {
            foreach ($targets as $to) {
                $sim = Board::createFromGame($game);

                $move = (new Move())
                    ->setFromSq($from)
                    ->setToSq($to)
                    ->setColor($color);

                $this->applyMoveOnBoard($sim, $move);

                if (!$this->isKingChecked($sim, $color)) {
                    $legal[$from][] = $to;
                }
            }
        }

        return $legal;
    }

    public function isKingChecked(Board $board, string $color): bool
    {
        return $this->isSquareAttacked(
            $board,
            $this->findKingSquare($board, $color),
            $color === 'white' ? 'black' : 'white'
        );
    }

    public function isCheck(Game $game, string $color): bool
    {
        return $this->isKingChecked($game->getBoard(), $color);
    }

    protected function findKingSquare(Board $board, string $color): string
    {
        foreach ($board->getPieces() as $piece) {
            if ($piece instanceof King && $piece->color === $color) {
                return $piece->square->toSquare();
            }
        }

        throw new \LogicException("King not found");
    }

    protected function isSquareAttacked(Board $board, string $square, string $attackerColor): bool
    {
        $sqObj = $board->getSquareFromNotation($square);

        foreach ($board->getPieces() as $piece) {
            if ($piece->color !== $attackerColor) {
                continue;
            }

            if ($piece->canMoveTo($sqObj, false)) {
                return true;
            }
        }

        return false;
    }
}
