<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\SpawnEquipmentEventConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Project\Entity\ProjectConfig;

final class ProjectConfigDataLoader extends ConfigDataLoader
{
    private EntityRepository $projectConfigRepository;
    private EntityRepository $modifierConfigRepository;
    private EntityRepository $eventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($entityManager);
        $this->projectConfigRepository = $entityManager->getRepository(ProjectConfig::class);
        $this->modifierConfigRepository = $entityManager->getRepository(AbstractModifierConfig::class);
        $this->eventConfigRepository = $entityManager->getRepository(AbstractEventConfig::class);
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
        $newProjectConfigData['activationEvents'] = [];

        foreach ($projectConfigData['modifierConfigs'] as $modifierConfigName) {
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found");
            }
            $newProjectConfigData['modifierConfigs'][] = $modifierConfig;
        }

        foreach ($projectConfigData['activationEvents'] as $activationEvent) {
            $event = $this->eventConfigRepository->findOneBy(['name' => $activationEvent]);
            if (!$event) {
                throw new \RuntimeException("EventConfig {$activationEvent} not found");
            }
            $newProjectConfigData['activationEvents'][] = $event;
        }

        return $newProjectConfigData;
    }
}
