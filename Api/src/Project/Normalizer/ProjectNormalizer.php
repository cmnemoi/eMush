<?php

declare(strict_types=1);

namespace Mush\Project\Normalizer;

use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Skill\Enum\SkillName;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    public function __construct(
        private readonly GearToolServiceInterface $gearToolService,
        private readonly TranslationServiceInterface $translationService
    ) {}

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof Project;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Project $project */
        $project = $object;

        if (\array_key_exists('normalizing_daedalus', $context)) {
            return $this->getNormalizedProjectForDaedalusContext($project);
        }

        return $this->getNormalizedProjectForTerminalContext($project, $format, $context);
    }

    private function getNormalizedProjectForDaedalusContext(Project $project): array
    {
        $language = $project->getDaedalus()->getLanguage();

        return [
            'type' => $this->translationService->translate(
                key: "{$project->getName()}.type",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'key' => $project->getName(),
            'name' => $this->translationService->translate(
                key: "{$project->getName()}.name",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: "{$project->getName()}.description",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'lore' => $this->translationService->translate(
                key: "{$project->getName()}.lore",
                parameters: [],
                domain: 'project',
                language: $language
            ),
        ];
    }

    private function getNormalizedProjectForTerminalContext(Project $project, ?string $format, array $context): array
    {
        $context[$project->getClassName()] = $project;

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $language = $project->getDaedalus()->getLanguage();
        $playerEfficiency = $currentPlayer->getEfficiencyForProject($project);

        return [
            'id' => $project->getId(),
            'key' => $project->getName(),
            'name' => $this->translationService->translate(
                key: "{$project->getName()}.name",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: "{$project->getName()}.description",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'lore' => $this->translationService->translate(
                key: "{$project->getName()}.lore",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'progress' => "{$project->getProgress()}%",
            'efficiency' => $this->translationService->translate(
                key: 'efficiency',
                parameters: [
                    'min_efficiency' => $playerEfficiency->min,
                    'max_efficiency' => $playerEfficiency->max,
                ],
                domain: 'project',
                language: $language
            ),
            'efficiencyTooltipHeader' => $this->translationService->translate(
                key: 'efficiency.tooltip.header',
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'efficiencyTooltipText' => $this->translationService->translate(
                key: 'efficiency.tooltip.text',
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'bonusSkills' => $this->getTranslatedSkills($project->getBonusSkills(), $language),
            'isLastAdvancedProject' => $project->isLastProjectAdvanced(),
            'actions' => $this->getNormalizedActions($project, ActionHolderEnum::PROJECT, $currentPlayer, $format, $context),
        ];
    }

    private function getTranslatedSkills(array $skills, string $language): array
    {
        return array_map(
            fn (SkillName $skill) => [
                'key' => $skill->toString(),
                'name' => $this->translationService->translate(
                    key: "{$skill->toString()}.name",
                    parameters: [],
                    domain: 'skill',
                    language: $language
                ),
                'description' => $this->translationService->translate(
                    key: "{$skill->toString()}.description",
                    parameters: [],
                    domain: 'skill',
                    language: $language
                ),
            ],
            $skills
        );
    }
}
