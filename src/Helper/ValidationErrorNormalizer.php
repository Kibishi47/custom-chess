<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationErrorNormalizer
{
    public function normalize(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath() ?: '_global';

            $errors[$property][] = [
                'message' => $violation->getMessage(),
                'code' => $violation->getCode(),
            ];
        }

        ksort($errors);

        return $errors;
    }

    public function buildErrorResponse(ConstraintViolationListInterface $violations, string $message = 'Validation failed'): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'errors' => $this->normalize($violations),
        ], 422);
    }
}
