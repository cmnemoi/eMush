<?php

declare(strict_types=1);

namespace Mush\Project\Normalizer;

use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private GearToolServiceInterface $gearToolService,
        private TranslationServiceInterface $translationService
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
        ];
    }

    private function getNormalizedProjectForTerminalContext(Project $project, ?string $format, array $context): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $language = $project->getDaedalus()->getLanguage();

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
            'progress' => "{$project->getProgress()}%",
            'efficiency' => $this->translationService->translate(
                key: 'efficiency',
                parameters: [
                    'min_efficiency' => $currentPlayer->getMinEfficiencyForProject($project),
                    'max_efficiency' => $currentPlayer->getMaxEfficiencyForProject($project),
                ],
                domain: 'project',
                language: $language
            ),
            'bonusSkills' => $this->getTranslatedSkills($project->getBonusSkills(), $language),
            'actions' => $this->getNormalizedProjectActions($project, $format, $context),
        ];
    }

    private function getNormalizedProjectActions(Project $project, ?string $format = null, array $context = []): array
    {
        $actions = [];
        $currentPlayer = $context['currentPlayer'];
        $context['project'] = $project;

        $toolsActions = $this->gearToolService->getActionsTools(
            player: $currentPlayer,
            scopes: [ActionScopeEnum::TERMINAL],
            target: Project::class,
        );

        foreach ($toolsActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (\is_array($normedAction) && \count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getTranslatedSkills(array $skills, string $language): array
    {
        return array_map(
            fn ($skill) => [
                'key' => $skill,
                'name' => $this->translationService->translate(
                    key: "{$skill}.name",
                    parameters: [],
                    domain: 'skill',
                    language: $language
                ),
                'description' => $this->translationService->translate(
                    key: "{$skill}.description",
                    parameters: [],
                    domain: 'skill',
                    language: $language
                ),
            ],
            $skills
        );
    }
}
