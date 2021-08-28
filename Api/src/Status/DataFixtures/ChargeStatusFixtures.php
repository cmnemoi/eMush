<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class ChargeStatusFixtures extends Fixture implements DependentFixtureInterface
{
    public const FIRE_STATUS = 'fire_status';
    public const TURRET_CHARGE = 'turret_charge';
    public const PATROLLER_CHARGE = 'patroller_charge';
    public const BLASTER_CHARGE = 'blaster_charge';
    public const OLD_FAITHFUL_CHARGE = 'old_faithful_charge';
    public const ANTIGRAV_SCOOTER_CHARGE = 'antigrav_scooter_charge';
    public const MICROWAVE_CHARGE = 'microwave_charge';
    public const DAILY_ELECTRIC_CHARGE = 'daily_electric_charge';
    public const CYCLE_ELECTRIC_CHARGE = 'cycle_electric_charge';
    public const PLANT_YOUNG = 'plant_young';
    public const EUREKA_MOMENT = 'eureka_moment';
    public const FIRST_TIME = 'first_time';
    public const SPORES = 'spores';
    public const MUSH_STATUS = 'mush_status';
    public const CONTAMINATED_FOOD = 'contaminated_food';
    public const COMBUSTION_CHAMBER = 'combustion_chamber';
    public const DRUG_EATEN_STATUS = 'drug_eaten_status';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $fireStatus = new ChargeStatusConfig();
        $fireStatus
            ->setName(StatusEnum::FIRE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
        ;

        $manager->persist($fireStatus);

        $turretCharge = new ChargeStatusConfig();
        $turretCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(4)
            ->setThreshold(4)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([EquipmentEnum::TURRET_COMMAND])
        ;
        $manager->persist($turretCharge);

        $patrollerCharge = new ChargeStatusConfig();
        $patrollerCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(10)
            ->setThreshold(10)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([EquipmentEnum::PATROL_SHIP])
        ;
        $manager->persist($patrollerCharge);

        $blasterCharge = new ChargeStatusConfig();
        $blasterCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(1)
            ->setThreshold(3)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([ItemEnum::BLASTER, ItemEnum::NATAMY_RIFLE])
        ;
        $manager->persist($blasterCharge);

        $oldFaithfulCharge = new ChargeStatusConfig();
        $oldFaithfulCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(12)
            ->setThreshold(12)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([ItemEnum::OLD_FAITHFUL])
        ;
        $manager->persist($oldFaithfulCharge);

        $antigravScooterCharge = new ChargeStatusConfig();
        $antigravScooterCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(2)
            ->setThreshold(8)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([GearItemEnum::ANTI_GRAV_SCOOTER])
        ;
        $manager->persist($antigravScooterCharge);

        $microwaveCharge = new ChargeStatusConfig();
        $microwaveCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(0)
            ->setThreshold(4)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([ToolItemEnum::MICROWAVE])
        ;
        $manager->persist($microwaveCharge);

        $dailyElectricCharge = new ChargeStatusConfig();
        $dailyElectricCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setStartingCharge(1)
            ->setThreshold(1)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([EquipmentEnum::NARCOTIC_DISTILLER, EquipmentEnum::COFFEE_MACHINE])
        ;
        $manager->persist($dailyElectricCharge);

        $cycleElectricCharge = new ChargeStatusConfig();
        $cycleElectricCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setStartingCharge(1)
            ->setThreshold(1)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([ItemEnum::NATAMY_RIFLE, ItemEnum::ROCKET_LAUNCHER])
        ;
        $manager->persist($cycleElectricCharge);

        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setStartingCharge(1)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($plantYoung);

        $eurekaMoment = new ChargeStatusConfig();
        $eurekaMoment
            ->setName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartingCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($eurekaMoment);

        $firstTime = new ChargeStatusConfig();
        $firstTime
            ->setName(PlayerStatusEnum::FIRST_TIME)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartingCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($firstTime);

        $spores = new ChargeStatusConfig();
        $spores
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setStartingCharge(1)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($spores);

        $mushStatus = new ChargeStatusConfig();
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setStartingCharge(1)
            ->setThreshold(1)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($mushStatus);

        $contaminated = new ChargeStatusConfig();
        $contaminated
            ->setName(EquipmentStatusEnum::CONTAMINATED)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($contaminated);

        $combustionChamber = new ChargeStatusConfig();
        $combustionChamber
            ->setName(EquipmentStatusEnum::FUEL_CHARGE)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setStartingCharge(0)
            ->setThreshold(9)
            ->setGameConfig($gameConfig)
            ->setApplyToEquipments([EquipmentEnum::COMBUSTION_CHAMBER])
        ;
        $manager->persist($combustionChamber);

        $drug_eaten = new ChargeStatusConfig();
        $drug_eaten
            ->setName(PlayerStatusEnum::DRUG_EATEN)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setStartingCharge(1)
            ->setThreshold(0)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($drug_eaten);

        $manager->flush();

        $this->addReference(self::FIRE_STATUS, $fireStatus);
        $this->addReference(self::TURRET_CHARGE, $turretCharge);
        $this->addReference(self::PATROLLER_CHARGE, $patrollerCharge);
        $this->addReference(self::BLASTER_CHARGE, $blasterCharge);
        $this->addReference(self::OLD_FAITHFUL_CHARGE, $oldFaithfulCharge);
        $this->addReference(self::ANTIGRAV_SCOOTER_CHARGE, $antigravScooterCharge);
        $this->addReference(self::MICROWAVE_CHARGE, $microwaveCharge);
        $this->addReference(self::DAILY_ELECTRIC_CHARGE, $dailyElectricCharge);
        $this->addReference(self::CYCLE_ELECTRIC_CHARGE, $cycleElectricCharge);
        $this->addReference(self::PLANT_YOUNG, $plantYoung);
        $this->addReference(self::EUREKA_MOMENT, $eurekaMoment);
        $this->addReference(self::FIRST_TIME, $firstTime);
        $this->addReference(self::SPORES, $spores);
        $this->addReference(self::MUSH_STATUS, $mushStatus);
        $this->addReference(self::CONTAMINATED_FOOD, $contaminated);
        $this->addReference(self::COMBUSTION_CHAMBER, $combustionChamber);
        $this->addReference(self::DRUG_EATEN_STATUS, $drug_eaten);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
