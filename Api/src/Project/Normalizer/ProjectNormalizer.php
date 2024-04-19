<?php

declare(strict_types=1);

namespace Mush\Project\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Project\Entity\Project;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProjectNormalizer implements NormalizerInterface
{
    public function __construct(
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
        $language = $project->getDaedalus()->getLanguage();

        return [
            'key' => $project->getName()->value,
            'name' => $this->translationService->translate(
                key: "{$project->getName()->value}.name",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'description' => $this->translationService->translate(
                key: "{$project->getName()->value}.description",
                parameters: [],
                domain: 'project',
                language: $language
            ),
            'progress' => "{$project->getProgress()}%",
            'efficiency' => $this->translationService->translate(
                key: 'efficiency',
                parameters: [
                    'min_efficiency' => $project->getMinEfficiency(),
                    'max_efficiency' => $project->getMaxEfficiency(),
                ],
                domain: 'project',
                language: $language
            ),
            'bonusSkills' => $this->getTranslatedSkills($project->getBonusSkills(), $language),
        ];
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
