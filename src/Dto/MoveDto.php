<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MoveDto
{
    public function __construct(
        #[Assert\NotBlank]
        private string $fromSq,

        #[Assert\NotBlank]
        private string $toSq,

        #[Assert\NotBlank]
        private string $color,

        #[Assert\NotBlank]
        private string $piece
    ) {}

    public function getFromSq(): string
    {
        return $this->fromSq;
    }

    public function getToSq(): string
    {
        return $this->toSq;
    }

    public function getColor(): string
    {
        return $this->color;
    }


    public function getPiece(): string
    {
        return $this->piece;
    }
}
