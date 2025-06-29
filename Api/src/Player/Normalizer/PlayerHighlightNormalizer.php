<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlayerHighlightNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof PlayerHighlight;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PlayerHighlight::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        /** @var PlayerHighlight $highlight */
        $highlight = $object;
        $language = $context['language'];

        return $this->translationService->translate(
            key: $highlight->toTranslationKey(),
            parameters: $highlight->toTranslationParameters(),
            domain: 'highlight',
            language: $language
        );
    }
}
