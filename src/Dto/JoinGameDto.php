<?php

namespace App\Dto;

use App\Chess\Board\BoardType;
use Symfony\Component\Validator\Constraints as Assert;

class JoinGameDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [BoardType::class, 'getValues'])]
        private string $boardType
    ) {}

    public function getBoardType(): BoardType
    {
        return BoardType::from($this->boardType);
    }
}
