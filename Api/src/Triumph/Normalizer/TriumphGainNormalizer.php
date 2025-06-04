<?php

declare(strict_types=1);

namespace Mush\Triumph\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Triumph\ValueObject\TriumphGain;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TriumphGainNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return $data instanceof TriumphGain;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TriumphGain::class => true,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): string
    {
        /** @var TriumphGain $triumphGain */
        $triumphGain = $object;

        $triumphName = $this->translationService->translate(
            key: $triumphGain->getTriumphKey()->toString() . '.name',
            parameters: [],
            domain: 'triumph',
            language: $context['language'],
        );

        return "{$triumphGain->getCount()} x {$triumphName} ( {$triumphGain->getValueAsString()} {$triumphGain->toEmoteCode()} )";
    }
}
