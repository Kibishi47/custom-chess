<?php

namespace App\Validator;

use App\Chess\Board\Board;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MoveIsLegal extends Constraint
{
    public string $message = 'Illegal move for this game.';
    public string $mode = 'strict';

    public Board $board;

    public function __construct(Board $board, ?string $mode = null, ?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->board = $board;
        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
