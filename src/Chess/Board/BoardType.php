<?php

namespace App\Chess\Board;

use App\Chess\Board\Type\StandardBoard;

enum BoardType: string
{
    case StandardBoard = 'standard';

    public function getClass(): string
    {
        return match ($this) {
            self::StandardBoard => StandardBoard::class,
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
