<?php

declare(strict_types=1);

namespace Mush\Project\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\DataFixtures\ReplaceEquipmentConfigFixtures;
use Mush\Equipment\DataFixtures\SpawnEquipmentConfigFixtures;
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
            $data = $this->getConfigDataWithSubConfigs($data);

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
            SpawnEquipmentConfigFixtures::class,
            ReplaceEquipmentConfigFixtures::class,
        ];
    }

    private function getConfigDataWithSubConfigs(array $projectConfigData): array
    {
        $newProjectConfigData = $projectConfigData;
        $newProjectConfigData['modifierConfigs'] = [];
        $newProjectConfigData['spawnEquipmentConfigs'] = [];
        $newProjectConfigData['replaceEquipmentConfigs'] = [];

        foreach ($projectConfigData['modifierConfigs'] as $modifierConfigName) {
            $modifierConfig = $this->getReference($modifierConfigName);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found");
            }
            $newProjectConfigData['modifierConfigs'][] = $modifierConfig;
        }

        foreach ($projectConfigData['spawnEquipmentConfigs'] as $spawnEquipmentConfigName) {
            $spawnEquipmentConfig = $this->getReference($spawnEquipmentConfigName);
            if (!$spawnEquipmentConfig) {
                throw new \RuntimeException("SpawnEquipmentConfig {$spawnEquipmentConfigName} not found");
            }
            $newProjectConfigData['spawnEquipmentConfigs'][] = $spawnEquipmentConfig;
        }

        foreach ($projectConfigData['replaceEquipmentConfigs'] as $replaceEquipmentConfigName) {
            $replaceEquipmentConfig = $this->getReference($replaceEquipmentConfigName);
            if (!$replaceEquipmentConfig) {
                throw new \RuntimeException("ReplaceEquipmentConfig {$replaceEquipmentConfigName} not found");
            }
            $newProjectConfigData['replaceEquipmentConfigs'][] = $replaceEquipmentConfig;
        }

        return $newProjectConfigData;
    }
}
