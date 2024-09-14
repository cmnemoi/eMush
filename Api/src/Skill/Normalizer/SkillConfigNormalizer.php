<?php

declare(strict_types=1);

namespace Mush\Skill\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Skill\Entity\SkillConfig;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SkillConfigNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SkillConfig;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $character = $this->character($context);
        $daedalus = $this->daedalus($context);

        /** @var SkillConfig $skillConfig */
        $skillConfig = $object;

        return [
            'key' => $skillConfig->getNameAsString(),
            'name' => $this->translationService->translate(
                key: $skillConfig->getNameAsString() . '.name',
                parameters: [$character->getLogKey() => $character->getName()],
                domain: 'skill',
                language: $daedalus->getLanguage()
            ),
            'description' => $this->translationService->translate(
                key: $skillConfig->getNameAsString() . '.description',
                parameters: [$character->getLogKey() => $character->getName()],
                domain: 'skill',
                language: $daedalus->getLanguage()
            ),
        ];
    }

    private function character(array $context): CharacterConfig
    {
        return $context['character'] ?? $context['currentPlayer']->getCharacterConfig() ?? throw new \RuntimeException('This normalizer requires a character in the context');
    }

    private function daedalus(array $context): Daedalus
    {
        return $context['daedalus'] ?? $context['currentPlayer']->getDaedalus() ?? throw new \RuntimeException('This normalizer requires a daedalus in the context');
    }
}
