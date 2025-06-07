<?php

declare(strict_types=1);

namespace Mush\Action\Normalizer;

use Mush\Action\ValueObject\ActionHighlight;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ActionHighlightNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof ActionHighlight;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ActionHighlight::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        /** @var ActionHighlight $highlight */
        $highlight = $object;
        $language = $context['language'];

        return $this->translationService->translate(
            key: $highlight->toLogKey(),
            parameters: $highlight->toTranslationParameters(),
            domain: 'actions',
            language: $language
        );
    }
}
