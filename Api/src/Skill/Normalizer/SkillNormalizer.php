<?php

declare(strict_types=1);

namespace Mush\Skill\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Skill\Entity\Skill;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SkillNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Skill;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Skill $skill */
        $skill = $object;
        $player = $skill->getPlayer();

        return [
            'key' => $skill->getNameAsString(),
            'name' => $this->translationService->translate(
                key: $skill->getNameAsString() . '.name',
                parameters: [$player->getLogKey() => $player->getLogName()],
                domain: 'skill',
                language: $player->getDaedalus()->getLanguage()
            ),
            'description' => $this->translationService->translate(
                key: $skill->getNameAsString() . '.description',
                parameters: [$player->getLogKey() => $player->getLogName()],
                domain: 'skill',
                language: $player->getDaedalus()->getLanguage()
            ),
            'isMushSkill' => $skill->isMushSkill(),
        ];
    }
}
