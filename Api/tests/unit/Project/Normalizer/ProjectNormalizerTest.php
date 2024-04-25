<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Project\Normalizer;

use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\InMemoryTranslationService;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\Normalizer\ProjectNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectNormalizerTest extends TestCase
{
    private ProjectNormalizer $projectNormalizer;

    /**
     * @before
     */
    public function before(): void
    {
        $this->projectNormalizer = new ProjectNormalizer(
            new InMemoryTranslationService([
                'pilgred.name' => 'PILGRED',
                'pilgred.description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
                'physicist.name' => 'Physicien',
                'physicist.description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des avantages pour réparer PILGRED.',
                'technician.name' => 'Technicien',
                'technician.description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.',
                'efficiency' => 'Efficacité : 1-1%',
            ])
        );
    }

    public function testShouldNormalizeProject(): void
    {
        // given I have a project
        $project = ProjectFactory::createPilgredProject();

        // when I normalize the project
        $normalizedProject = $this->projectNormalizer->normalize($project);

        // then I should get the normalized project
        self::assertEquals(
            expected: [
                'key' => ProjectName::PILGRED->value,
                'name' => 'PILGRED',
                'description' => 'Réparer PILGRED vous permettra d\'ouvrir de nouvelles routes spatiales, dont celle vers la Terre.',
                'progress' => '0%',
                'efficiency' => 'Efficacité : 1-1%',
                'bonusSkills' => [
                    [
                        'key' => SkillEnum::PHYSICIST,
                        'name' => 'Physicien',
                        'description' => 'Le physicien est un chercheur en physique de haut vol, sa compréhension des mécaniques quantiques et de l\'essence même des cordes qui composent notre Univers est son atout. Il possède des avantages pour réparer PILGRED.',
                    ],
                    [
                        'key' => SkillEnum::TECHNICIAN,
                        'name' => 'Technicien',
                        'description' => 'Le Technicien est qualifié pour réparer le matériel, les équipements et la coque du Daedalus.',
                    ],
                ],
            ],
            actual: $normalizedProject
        );
    }
}
