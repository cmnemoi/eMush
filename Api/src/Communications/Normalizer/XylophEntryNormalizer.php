<?php

declare(strict_types=1);

namespace Mush\Communications\Normalizer;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class XylophEntryNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof XylophEntry;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [XylophEntry::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $language = $this->getLanguageFromContext($context);
        $xylophEntry = $this->getXylophEntry($object);

        $key = $xylophEntry->isDecoded() ? $xylophEntry->getName()->toString() : XylophEnum::UNKNOWN->toString();

        return [
            'key' => $xylophEntry->getName()->toString(),
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $key),
                parameters: [],
                domain: 'xyloph_entry',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: \sprintf('%s.description', $key),
                parameters: [],
                domain: 'xyloph_entry',
                language: $language
            ),
            'isDecoded' => $xylophEntry->isDecoded(),
        ];
    }

    private function getLanguageFromContext(array $context): string
    {
        $player = $context['currentPlayer'] ?? throw new \Exception('Current player not found in context');

        return $player->getLanguage();
    }

    private function getXylophEntry(mixed $object): XylophEntry
    {
        return $object instanceof XylophEntry ? $object : throw new \Exception('Object is not a XylophEntry');
    }
}
