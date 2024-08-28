<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ContactablePlayerNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Player && \array_key_exists('currentPlayer', $context) === false;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $player = $this->player($object);

        return [
            'key' => $player->getName(),
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $player->getName()),
                parameters: [],
                domain: 'characters',
                language: $player->getLanguage(),
            ),
        ];
    }

    private function player(mixed $object): Player
    {
        return $object instanceof Player ? $object : throw new \InvalidArgumentException('This normalizer only supports Player objects');
    }
}
