<?php

namespace App\Enum;

enum GameStatus: string
{
    case WAITING = 'waiting';
    case ONGOING = 'ongoing';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';
}
