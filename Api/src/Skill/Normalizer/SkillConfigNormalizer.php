<?php

declare(strict_types=1);

namespace Mush\Skill\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
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
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var SkillConfig $skillConfig */
        $skillConfig = $object;

        return [
            'key' => $skillConfig->getNameAsString(),
            'name' => $this->translationService->translate(
                key: $skillConfig->getNameAsString() . '.name',
                parameters: [$currentPlayer->getLogKey() => $currentPlayer->getLogName()],
                domain: 'skill',
                language: $currentPlayer->getDaedalus()->getLanguage()
            ),
            'description' => $this->translationService->translate(
                key: $skillConfig->getNameAsString() . '.description',
                parameters: [$currentPlayer->getLogKey() => $currentPlayer->getLogName()],
                domain: 'skill',
                language: $currentPlayer->getDaedalus()->getLanguage()
            ),
        ];
    }
}
