<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\SpaceShipConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;

class PatrolShipConfigFixtures extends EquipmentConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (EquipmentConfigData::$dataArray as $patrolShipData) {
            if ($patrolShipData['type'] !== 'patrol_ship') {
                continue;
            }

            $patrolShipConfig = new SpaceShipConfig();

            $this->setEquipmentConfigAttributes($patrolShipConfig, $patrolShipData);
            $this->setEquipmentConfigActions($patrolShipConfig, $patrolShipData, $manager);
            $this->setEquipmentConfigMechanics($patrolShipConfig, $patrolShipData, $manager);
            $this->setEquipmentConfigStatusConfigs($patrolShipConfig, $patrolShipData, $manager);

            $patrolShipConfig
                ->setCollectScrapNumber($patrolShipData['collectScrapNumber'])
                ->setCollectScrapPatrolShipDamage([1 => 3])
                ->setCollectScrapPlayerDamage([1 => 3])
                ->setFailedManoeuvreDaedalusDamage([1 => 3])
                ->setFailedManoeuvrePatrolShipDamage([1 => 3])
                ->setFailedManoeuvrePlayerDamage([1 => 3])
                ->setNumberOfExplorationSteps($patrolShipData['numberOfExplorationSteps']);

            $manager->persist($patrolShipConfig);

            $this->addReference($patrolShipConfig->getName(), $patrolShipConfig);

            $gameConfig->addEquipmentConfig($patrolShipConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
        ];
    }
}
