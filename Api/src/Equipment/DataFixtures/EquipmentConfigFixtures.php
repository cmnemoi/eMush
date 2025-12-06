<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\ModifierConfigFixtures;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (EquipmentConfigData::$dataArray as $equipmentConfigData) {
            if ($equipmentConfigData['type'] !== 'equipment_config') {
                continue;
            }

            $equipmentConfig = new EquipmentConfig();

            $this->setEquipmentConfigAttributes($equipmentConfig, $equipmentConfigData);
            $this->setEquipmentConfigActions($equipmentConfig, $equipmentConfigData, $manager);
            $this->setEquipmentConfigMechanics($equipmentConfig, $equipmentConfigData, $manager);
            $this->setEquipmentConfigStatusConfigs($equipmentConfig, $equipmentConfigData, $manager);

            $manager->persist($equipmentConfig);

            $this->addReference($equipmentConfig->getName(), $equipmentConfig);

            $gameConfig->addEquipmentConfig($equipmentConfig);
        }
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
            TechnicianFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
            ModifierConfigFixtures::class,
        ];
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

    protected function setEquipmentConfigActions(EquipmentConfig $equipmentConfig, array $equipmentConfigData, ObjectManager $manager): void
    {
        $actions = [];
        foreach ($equipmentConfigData['actions'] as $actionName) {
            /** @var ActionConfig $action */
            $action = $manager->getRepository(ActionConfig::class)->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $equipmentConfig->setActionConfigs($actions);
    }

    protected function setEquipmentConfigMechanics(EquipmentConfig $equipmentConfig, array $equipmentConfigData, ObjectManager $manager): void
    {
        $mechanics = [];
        foreach ($equipmentConfigData['mechanics'] as $mechanicName) {
            /** @var Mechanics $mechanic */
            $mechanic = $manager->getRepository(Mechanics::class)->findOneBy(['name' => $mechanicName]);
            if ($mechanic === null) {
                throw new \Exception('Mechanics not found: ' . $mechanicName);
            }
            $mechanics[] = $mechanic;
        }
        $equipmentConfig->setMechanics($mechanics);
    }

    protected function setEquipmentConfigStatusConfigs(EquipmentConfig $equipmentConfig, array $equipmentConfigData, ObjectManager $manager): void
    {
        $statusConfigs = [];
        foreach ($equipmentConfigData['initStatuses'] as $statusConfigName) {
            if ($statusConfigName === 'broken_default') {
                continue;
            }

            $statusConfig = $manager->getRepository(StatusConfig::class)->findOneBy(['name' => $statusConfigName]);
            if ($statusConfig === null) {
                throw new \Exception('Status configs not found: ' . $statusConfigName);
            }
            $statusConfigs[] = $statusConfig;
        }
        $equipmentConfig->setInitStatuses($statusConfigs);
    }
}
