<?php

namespace App\Validator;

use App\Chess\Board\Board;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MoveIsLegalValidator extends ConstraintValidator
{
    public function validate($dto, Constraint $constraint): void
    {
        if (!$constraint instanceof MoveIsLegal) {
            return;
        }

        /** @var Board $board */
        $board = $constraint->board;

        $moves = $board->generateMovesForColor($dto->getColor());

        $from = $dto->getFromSq();
        $to = $dto->getToSq();

        if (!isset($moves[$from]) || !in_array($to, $moves[$from])) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
