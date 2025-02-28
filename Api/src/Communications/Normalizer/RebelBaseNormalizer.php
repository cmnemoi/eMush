<?php

namespace Mush\Communications\Normalizer;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RebelBaseNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof RebelBase;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [RebelBase::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $language = $this->getLanguageFromContext($context);
        $rebelBase = $this->getRebelBase($object);

        $keyToTranslate = $this->getKeyToTranslate($rebelBase);
        $descriptionKey = $this->getDescriptionKey($rebelBase);

        return [
            'key' => $rebelBase->getName()->toString(),
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $keyToTranslate),
                parameters: [],
                domain: 'rebel_base',
                language: $language
            ),
            'hoverName' => $this->translationService->translate(
                key: \sprintf('%s.hoverName', $keyToTranslate),
                parameters: [],
                domain: 'rebel_base',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: \sprintf('%s.description', $descriptionKey),
                parameters: [],
                domain: 'rebel_base',
                language: $language
            ),
            'signal' => \sprintf('%s%%', $rebelBase->getSignal()),
            'isContacting' => $rebelBase->isContacting(),
            'isLost' => $rebelBase->isLost(),
        ];
    }

    private function getLanguageFromContext(array $context): string
    {
        $player = $context['currentPlayer'] ?? throw new \Exception('Current player not found in context');

        return $player->getLanguage();
    }

    private function getRebelBase(mixed $object): RebelBase
    {
        return $object instanceof RebelBase ? $object : throw new \Exception('Object is not a RebelBase');
    }

    private function getKeyToTranslate(RebelBase $rebelBase): string
    {
        return $rebelBase->isDecoded() ? $rebelBase->getName()->toString() : RebelBaseEnum::UNKNOWN->toString();
    }

    private function getDescriptionKey(RebelBase $rebelBase): string
    {
        return match (true) {
            $rebelBase->isDecoded() => $rebelBase->getName()->toString(),
            $rebelBase->isLost() => 'lost',
            $rebelBase->isContacting() => 'contacting',
            $rebelBase->isNotContacting() => 'not_contacting',
            default => throw new \Exception('Unknown rebel base status'),
        };
    }
}
