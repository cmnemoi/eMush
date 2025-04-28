<?php

namespace Mush\Equipment\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Repository\ActionConfigRepository;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;
use Mush\Equipment\Repository\EquipmentConfigRepository;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\MetaGame\Entity\Skin\SkinSlotConfig;
use Mush\Status\Repository\StatusConfigRepository;

class EquipmentConfigDataLoader extends ConfigDataLoader
{
    protected EquipmentConfigRepository $equipmentConfigRepository;
    protected ActionConfigRepository $actionConfigRepository;
    protected MechanicsRepository $mechanicsRepository;
    protected StatusConfigRepository $statusConfigRepository;
    protected EntityRepository $skinSlotConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EquipmentConfigRepository $equipmentConfigRepository,
        ActionConfigRepository $actionConfigRepository,
        MechanicsRepository $mechanicsRepository,
        StatusConfigRepository $statusConfigRepository
    ) {
        parent::__construct($entityManager);
        $this->equipmentConfigRepository = $equipmentConfigRepository;
        $this->actionConfigRepository = $actionConfigRepository;
        $this->mechanicsRepository = $mechanicsRepository;
        $this->statusConfigRepository = $statusConfigRepository;
        $this->skinSlotConfigRepository = $entityManager->getRepository(SkinSlotConfig::class);
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
            $this->setSkinSlotConfig($equipmentConfig, $equipmentConfigData);

            $this->entityManager->persist($equipmentConfig);
        }
        $this->entityManager->flush();
    }

    protected function setEquipmentConfigAttributes(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $equipmentConfig
            ->setName($equipmentConfigData['name'])
            ->setEquipmentName($equipmentConfigData['equipmentName'])
            ->setBreakableType($equipmentConfigData['breakableType'])
            ->setDismountedProducts($equipmentConfigData['dismountedProducts'])
            ->setIsPersonal($equipmentConfigData['isPersonal']);
    }

    protected function setEquipmentConfigActions(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        $actions = [];
        foreach ($equipmentConfigData['actions'] as $actionName) {
            /** @var ActionConfig $action */
            $action = $this->actionConfigRepository->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $equipmentConfig->setActionConfigs($actions);
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

    protected function setSkinSlotConfig(EquipmentConfig $equipmentConfig, array $equipmentConfigData): void
    {
        if (\array_key_exists('skinSlotsConfig', $equipmentConfigData)) {
            foreach ($equipmentConfigData['skinSlotsConfig'] as $skinSlotName) {
                $skinSlotConfig = $this->skinSlotConfigRepository->findOneBy(['name' => $skinSlotName]);
                if ($skinSlotConfig === null) {
                    throw new \Exception('Skin slot configs not found: ' . $skinSlotName);
                }
                $equipmentConfig->addSkinSlot($skinSlotConfig);
            }
        }
    }
}
