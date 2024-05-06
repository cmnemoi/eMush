<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Project\Entity\ProjectConfig;

final class ProjectConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $projectConfigRepository;
    private EntityRepository $modifierConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->projectConfigRepository = $entityManager->getRepository(ProjectConfig::class);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
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
        // TODO Add activationEvents

        foreach ($projectConfigData['modifierConfigs'] as $modifierConfigName) {
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found");
            }
            $newProjectConfigData['modifierConfigs'][] = $modifierConfig;
        }

        return $newProjectConfigData;
    }
}
