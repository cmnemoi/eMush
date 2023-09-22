<?php

namespace Mush\Equipment\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Repository\ActionRepository;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;
use Mush\Equipment\Repository\EquipmentConfigRepository;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Status\Repository\StatusConfigRepository;

class EquipmentConfigDataLoader extends ConfigDataLoader
{
    protected EquipmentConfigRepository $equipmentConfigRepository;
    protected ActionRepository $actionRepository;
    protected MechanicsRepository $mechanicsRepository;
    protected StatusConfigRepository $statusConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EquipmentConfigRepository $equipmentConfigRepository,
        ActionRepository $actionRepository,
        MechanicsRepository $mechanicsRepository,
        StatusConfigRepository $statusConfigRepository)
    {
        parent::__construct($entityManager);
        $this->equipmentConfigRepository = $equipmentConfigRepository;
        $this->actionRepository = $actionRepository;
        $this->mechanicsRepository = $mechanicsRepository;
        $this->statusConfigRepository = $statusConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (EquipmentConfigData::$dataArray as $equipmentConfigData) {
            if ($equipmentConfigData['type'] !== 'equipment_config') {
                continue;
            }

            $equipmentConfig = $this->equipmentConfigRepository->findOneBy(['name' => $equipmentConfigData['name']]);

            if ($equipmentConfig === null) {
                $equipmentConfig = new EquipmentConfig();
            }

            $this->setEquipmentConfigAttributes($equipmentConfig, $equipmentConfigData);
            $this->setEquipmentConfigActions($equipmentConfig, $equipmentConfigData);
            $this->setEquipmentConfigMechanics($equipmentConfig, $equipmentConfigData);
            $this->setEquipmentConfigStatusConfigs($equipmentConfig, $equipmentConfigData);

            $this->entityManager->persist($equipmentConfig);
        }
        $this->entityManager->flush();
    }

    protected function setEquipmentConfigAttributes(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $equipmentConfig
            ->setName($equipmentConfigData['name'])
            ->setEquipmentName($equipmentConfigData['equipmentName'])
            ->setIsBreakable($equipmentConfigData['isBreakable'])
            ->setIsFireBreakable($equipmentConfigData['isFireBreakable'])
            ->setIsFireDestroyable($equipmentConfigData['isFireDestroyable'])
            ->setDismountedProducts($equipmentConfigData['dismountedProducts'])
            ->setIsPersonal($equipmentConfigData['isPersonal'])
        ;
    }

    protected function setEquipmentConfigActions(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $actions = [];
        foreach ($equipmentConfigData['actions'] as $actionName) {
            /** @var Action $action */
            $action = $this->actionRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('Action not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $equipmentConfig->setActions($actions);
    }

    protected function setEquipmentConfigMechanics(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $mechanics = [];
        foreach ($equipmentConfigData['mechanics'] as $mechanicName) {
            /** @var Mechanics $mechanic */
            $mechanic = $this->mechanicsRepository->findOneBy(['name' => $mechanicName]);
            if ($mechanic === null) {
                throw new \Exception('Mechanics not found: ' . $mechanicName);
            }
            $mechanics[] = $mechanic;
        }
        $equipmentConfig->setMechanics($mechanics);
    }

    protected function setEquipmentConfigStatusConfigs(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $statusConfigs = [];
        foreach ($equipmentConfigData['initStatuses'] as $statusConfigName) {
            $statusConfig = $this->statusConfigRepository->findOneBy(['name' => $statusConfigName]);
            if ($statusConfig === null) {
                throw new \Exception('Status configs not found: ' . $statusConfigName);
            }
            $statusConfigs[] = $statusConfig;
        }
        $equipmentConfig->setInitStatuses($statusConfigs);
    }
}
