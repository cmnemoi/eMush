<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Project\Entity\ProjectConfig;

final class ProjectConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $projectConfigRepository;
    private EntityRepository $modifierConfigRepository;
    private EntityRepository $spawnEquipmentConfigRepository;

    private EntityRepository $replaceEquipmentConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->projectConfigRepository = $entityManager->getRepository(ProjectConfig::class);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
        $this->spawnEquipmentConfigRepository = $entityManager->getRepository(SpawnEquipmentConfig::class);
        $this->replaceEquipmentConfigRepository = $entityManager->getRepository(ReplaceEquipmentConfig::class);
    }

    public function loadConfigsData(): void
    {
        foreach (ProjectConfigData::getAll() as $projectConfigData) {
            /** @var ProjectConfig $projectConfig */
            $projectConfig = $this->projectConfigRepository->findOneBy(['name' => $projectConfigData['name']]);

            $projectConfigData = $this->getConfigDataWithAllSubConfigs($projectConfigData);

            if (!$projectConfig) {
                $projectConfig = new ProjectConfig(...$projectConfigData);
            } else {
                $projectConfig->updateFromConfigData($projectConfigData);
            }

            $this->entityManager->persist($projectConfig);
        }

        $this->entityManager->flush();
    }

    private function getConfigDataWithAllSubConfigs(array $projectConfigData): array
    {
        $newProjectConfigData = $projectConfigData;
        $newProjectConfigData['modifierConfigs'] = [];
        $newProjectConfigData['spawnEquipmentConfigs'] = [];
        $newProjectConfigData['replaceEquipmentConfigs'] = [];

        foreach ($projectConfigData['modifierConfigs'] as $modifierConfigName) {
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found");
            }
            $newProjectConfigData['modifierConfigs'][] = $modifierConfig;
        }

        foreach ($projectConfigData['spawnEquipmentConfigs'] as $spawnEquipmentName) {
            $spawnEquipmentConfig = $this->spawnEquipmentConfigRepository->findOneBy(['name' => $spawnEquipmentName]);
            if (!$spawnEquipmentConfig) {
                throw new \RuntimeException("SpawnEquipmentConfig {$spawnEquipmentName} not found");
            }
            $newProjectConfigData['spawnEquipmentConfigs'][] = $spawnEquipmentConfig;
        }

        foreach ($projectConfigData['replaceEquipmentConfigs'] as $replaceEquipmentConfigName) {
            $replaceEquipmentConfig = $this->replaceEquipmentConfigRepository->findOneBy(['name' => $replaceEquipmentConfigName]);
            if (!$replaceEquipmentConfig) {
                throw new \RuntimeException("ReplaceEquipmentConfig {$replaceEquipmentConfigName} not found");
            }
            $newProjectConfigData['replaceEquipmentConfigs'][] = $replaceEquipmentConfig;
        }

        return $newProjectConfigData;
    }
}
