<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SelectableCharacterNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CharacterConfig;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $character = $this->selectableCharacter($object);
        $daedalus = $this->daedalus($context);

        return [
            'key' => $character->getName(),
            'name' => $this->translationService->translate(
                key: $character->getName() . '.name',
                parameters: [],
                domain: 'characters',
                language : $daedalus->getLanguage()
            ),
            'abstract' => $this->translationService->translate(
                key: $character->getName() . '.abstract',
                parameters: [],
                domain: 'characters',
                language: $daedalus->getLanguage()
            ),
            'skills' => $this->normalizer->normalize($character->getSkillConfigs(), context: $context),
        ];
    }

    private function daedalus(array $context): Daedalus
    {
        return $context['daedalus'] ?? throw new \RuntimeException('This normalizer requires a daedalus in the context');
    }

    private function selectableCharacter(mixed $object): CharacterConfig
    {
        return $object instanceof CharacterConfig ? $object : throw new \RuntimeException('This normalizer only supports CharacterConfig');
    }
}
