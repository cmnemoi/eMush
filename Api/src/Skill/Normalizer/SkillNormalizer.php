<?php

declare(strict_types=1);

namespace Mush\Skill\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SkillNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Skill;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Skill::class => true,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Skill $skill */
        $skill = $object;
        $player = $skill->getPlayer();

        $skillKey = $player->hasStatus(PlayerStatusEnum::DISABLED) && $skill->getName() === SkillEnum::SPRINTER
            ? SkillEnum::DISABLED_SPRINTER->value
            : $skill->getNameAsString();

        return [
            'key' => $skillKey,
            'name' => $this->translationService->translate(
                key: $skill->getNameAsString() . '.name',
                parameters: [$player->getLogKey() => $player->getLogName()],
                domain: 'skill',
                language: $player->getLanguage(),
            ),
            'description' => $this->translationService->translate(
                key: $skill->getNameAsString() . '.description',
                parameters: [$player->getLogKey() => $player->getLogName()],
                domain: 'skill',
                language: $player->getLanguage(),
            ),
            'isMushSkill' => $skill->isMushSkill(),
        ];
    }
}
