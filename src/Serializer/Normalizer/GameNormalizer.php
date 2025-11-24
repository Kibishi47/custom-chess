<?php

namespace App\Serializer\Normalizer;

use App\Chess\Engine\GameEngine;
use App\Chess\Piece\Piece;
use App\Entity\Game;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GameNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
        private readonly GameEngine $engine,
    ) {}

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        /** @var Game $game */
        $game = $data;
        $normalizedData = $this->normalizer->normalize($game, $format, $context);

        if (isset($context['groups']) && in_array('game.info', $context['groups'])) {
            $turnColor = $game->getTurnColor();

            $normalizedData['turnColor'] = $game->getTurnColor();
            $normalizedData['pieces'] = array_map(
                function (Piece $piece) {
                    return $piece->toArray();
                },
                $game->getBoard()->getPieces()
            );

            $normalizedData['legalMoves'] = $this->engine->generateLegalMoves($game, $turnColor);

            $normalizedData['check'] = [
                'white' => $this->engine->isCheck($game, 'white'),
                'black' => $this->engine->isCheck($game, 'black'),
            ];
        }

        return $normalizedData;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Game;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Game::class => true,
        ];
    }
}
