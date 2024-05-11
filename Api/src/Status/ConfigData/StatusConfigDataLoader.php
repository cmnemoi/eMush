<?php

namespace Mush\Status\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Repository\ActionRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Repository\ModifierConfigRepository;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Repository\StatusConfigRepository;

class StatusConfigDataLoader extends ConfigDataLoader
{
    protected StatusConfigRepository $statusConfigRepository;
    protected ModifierConfigRepository $modifierConfigRepository;
    protected ActionRepository $actionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StatusConfigRepository $statusConfigRepository,
        ModifierConfigRepository $modifierConfigRepository,
        ActionRepository $actionRepository
    ) {
        parent::__construct($entityManager);
        $this->statusConfigRepository = $statusConfigRepository;
        $this->modifierConfigRepository = $modifierConfigRepository;
        $this->actionRepository = $actionRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (StatusConfigData::$dataArray as $statusConfigData) {
            if ($statusConfigData['type'] !== 'status_config') {
                continue;
            }
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigData['name']]);

            if ($statusConfig === null) {
                $statusConfig = new StatusConfig();
            }

            $statusConfig
                ->setName($statusConfigData['name'])
                ->setStatusName($statusConfigData['statusName'])
                ->setVisibility($statusConfigData['visibility']);
            $this->setStatusConfigModifierConfigs($statusConfig, $statusConfigData['modifierConfigs']);
            $this->setStatusConfigActionConfigs($statusConfig, $statusConfigData['actionConfigs']);

            $this->entityManager->persist($statusConfig);
        }
        $this->entityManager->flush();
    }

    protected function setStatusConfigModifierConfigs(StatusConfig $statusConfig, array $modifierConfigsArray): void
    {
        $modifierConfigs = [];
        foreach ($modifierConfigsArray as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception("Modifier config {$modifierConfigName} not found");
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $statusConfig->setModifierConfigs($modifierConfigs);
    }

    protected function setStatusConfigActionConfigs(StatusConfig $statusConfig, array $actionConfigsArray): void
    {
        $actionConfigs = [];
        foreach ($actionConfigsArray as $actionConfigName) {
            /** @var ActionConfig $actionConfig */
            $actionConfig = $this->actionRepository->findOneBy(['name' => $actionConfigName]);
            if ($actionConfig === null) {
                throw new \Exception("Action config {$actionConfigName} not found");
            }
            $actionConfigs[] = $actionConfig;
        }
        $statusConfig->setActionConfigs($actionConfigs);
    }
}
