<?php

declare(strict_types=1);

namespace Mush\Project\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\ProjectModifierConfigFixtures;
use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\Entity\ProjectConfig;

/** @codeCoverageIgnore */
final class ProjectConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $projectConfigs = [];

        foreach (ProjectConfigData::getAll() as $data) {
            $data = $this->getConfigDataWithModifierConfigs($data);

            $projectConfig = new ProjectConfig(...$data);
            $projectConfigs[] = $projectConfig;

            $manager->persist($projectConfig);
        }

        $gameConfig->setProjectConfigs($projectConfigs);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectModifierConfigFixtures::class,
        ];
    }

    private function getConfigDataWithModifierConfigs(array $projectConfigData): array
    {
        $newProjectConfigData = $projectConfigData;
        $newProjectConfigData['modifierConfigs'] = [];
        $newProjectConfigData['activationEvents'] = [];

        foreach ($projectConfigData['modifierConfigs'] as $modifierConfigName) {
            $modifierConfig = $this->getReference($modifierConfigName);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found");
            }
            $newProjectConfigData['modifierConfigs'][] = $modifierConfig;
        }

        foreach ($projectConfigData['activationEvents'] as $activationEvent) {
            $eventConfig = $this->getReference($activationEvent);
            if (!$eventConfig) {
                throw new \RuntimeException("ModifierConfig {$eventConfig->getName()} not found");
            }
            $newProjectConfigData['activationEvents'][] = $eventConfig;
        }

        return $newProjectConfigData;
    }
}
