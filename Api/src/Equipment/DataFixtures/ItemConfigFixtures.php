<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\DroneConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const METAL_SCRAPS = 'metal_scraps';
    public const PLASTIC_SCRAPS = 'plastic_scraps';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ArrayCollection $hideableActions */
        $hideableActions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var ActionConfig $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var ActionConfig $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ActionConfig $dismantle50 */
        $dismantle50 = $this->getReference(TechnicianFixtures::DISMANTLE_3_50);

        /** @var ActionConfig $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var ArrayCollection $mycoAlarmeActions */
        $mycoAlarmeActions = clone $hideableActions;
        $mycoAlarmeActions->add($dismantle25);
        $mycoAlarmeActions->add($repair25);
        $mycoAlarmeActions->add($sabotage25);

        $mycoAlarm = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::MYCO_ALARM));
        $mycoAlarm
            ->setActionConfigs($mycoAlarmeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mycoAlarm);

        /** @var ActionConfig $strengthenAction */
        $strengthenAction = $this->getReference(ActionsFixtures::STRENGTHEN_HULL);
        $metalScrapsAction = clone $hideableActions;
        $metalScrapsAction->add($strengthenAction);

        $metalScraps = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::METAL_SCRAPS));
        $metalScraps
            ->setActionConfigs($metalScrapsAction)
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($metalScraps);

        $plasticScraps = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::PLASTIC_SCRAPS));
        $plasticScraps
            ->setActionConfigs($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plasticScraps);

        $oldTShirt = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::OLD_T_SHIRT));
        $oldTShirt
            ->setActionConfigs($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($oldTShirt);

        $thickTubeActions = clone $hideableActions;
        $thickTubeActions->add($dismantle50);

        $thickTube = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::THICK_TUBE));
        $thickTube
            ->setActionConfigs($thickTubeActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($thickTube);

        $mushDiskActions = clone $hideableActions;
        $mushDiskActions->add($dismantle50);
        $mushDiskActions->add($repair25);
        $mushDiskActions->add($sabotage25);

        $mushDisk = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::MUSH_GENOME_DISK));
        $mushDisk
            ->setActionConfigs($mushDiskActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mushDisk);

        $mushSample = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::MUSH_SAMPLE));
        $mushSample
            ->setActionConfigs($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mushSample);

        $starmapFragment = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::STARMAP_FRAGMENT));
        $starmapFragment
            ->setActionConfigs($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($starmapFragment);

        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);

        $waterStick = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::WATER_STICK));
        $waterStick
            ->setActionConfigs($hideableActions)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($waterStick);

        $hydropot = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::HYDROPOT));
        $hydropot
            ->setActionConfigs($hideableActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hydropot);

        $oxygenCapsule = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::OXYGEN_CAPSULE));
        $oxygenCapsule
            ->setActionConfigs([$takeAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($oxygenCapsule);

        /** @var ActionConfig $insertFuelChamber */
        $insertFuelChamber = $this->getReference(ActionsFixtures::INSERT_FUEL_CHAMBER);
        $fuelCapsule = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::FUEL_CAPSULE));
        $fuelCapsule
            ->setActionConfigs([$takeAction, $examineAction, $insertFuelChamber])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($fuelCapsule);

        /** @var ChargeStatusConfig $droneCharges */
        $droneCharges = $this->getReference(EquipmentStatusEnum::ELECTRIC_CHARGES . '_' . ItemEnum::SUPPORT_DRONE);

        /** @var ActionConfig $upgradeDroneToTurbo */
        $upgradeDroneToTurbo = $this->getReference(ActionEnum::UPGRADE_DRONE_TO_TURBO->value);

        /** @var ActionConfig $upgradeDroneToFirefighter */
        $upgradeDroneToFirefighter = $this->getReference(ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER->value);

        /** @var ActionConfig $upgradeDroneToPilot */
        $upgradeDroneToPilot = $this->getReference(ActionEnum::UPGRADE_DRONE_TO_PILOT->value);

        /** @var ActionConfig $upgradeDroneToSensor */
        $upgradeDroneToSensor = $this->getReference(ActionEnum::UPGRADE_DRONE_TO_SENSOR->value);

        $drone = new DroneConfig();
        $drone
            ->setEquipmentName(ItemEnum::SUPPORT_DRONE)
            ->setIsStackable(false)
            ->setBreakableType(BreakableTypeEnum::BREAKABLE)
            ->setActionConfigs([$takeAction, $examineAction, $dropAction, $repair12, $upgradeDroneToTurbo, $upgradeDroneToFirefighter, $upgradeDroneToPilot, $upgradeDroneToSensor])
            ->setInitStatuses([$droneCharges])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($drone);

        /** @var ActionConfig $takeCatAction */
        $takeCatAction = $this->getReference(ActionsFixtures::TAKE_CAT);

        /** @var ActionConfig $petCatAction */
        $petCatAction = $this->getReference(ActionsFixtures::PET_CAT);

        /** @var ActionConfig $convertCatAction */
        $convertCatAction = $this->getReference(ActionEnum::CONVERT_CAT->value);

        $schrodinger = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::SCHRODINGER));
        $schrodinger
            ->setActionConfigs([$takeCatAction, $petCatAction, $examineAction, $convertCatAction, $dropAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($schrodinger);

        /** @var ActionConfig $playWithDogAction */
        $playWithDogAction = $this->getReference(ActionEnum::PLAY_WITH_DOG->value);

        $pavlov = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::PAVLOV));
        $pavlov
            ->setActionConfigs([$examineAction, $playWithDogAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pavlov);

        // @TODO add coffee thermos, lunchbox, survival kit

        $gameConfig
            ->addEquipmentConfig($mycoAlarm)
            ->addEquipmentConfig($plasticScraps)
            ->addEquipmentConfig($metalScraps)
            ->addEquipmentConfig($oldTShirt)
            ->addEquipmentConfig($thickTube)
            ->addEquipmentConfig($mushSample)
            ->addEquipmentConfig($mushDisk)
            ->addEquipmentConfig($starmapFragment)
            ->addEquipmentConfig($waterStick)
            ->addEquipmentConfig($hydropot)
            ->addEquipmentConfig($oxygenCapsule)
            ->addEquipmentConfig($fuelCapsule)
            ->addEquipmentConfig($drone)
            ->addEquipmentConfig($schrodinger)
            ->addEquipmentConfig($pavlov);
        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference(self::METAL_SCRAPS, $metalScraps);
        $this->addReference(self::PLASTIC_SCRAPS, $plasticScraps);
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
