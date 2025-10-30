<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MessageDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public readonly string $message,
    )
    {
    }
}
