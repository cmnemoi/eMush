<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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

        $electricCharge = new ChargeStatusConfig();
        $electricCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($electricCharge);

        $dailyElectricCharge = new ChargeStatusConfig();
        $dailyElectricCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($dailyElectricCharge);

        $plantYoung = new ChargeStatusConfig();
        $plantYoung
            ->setName(EquipmentStatusEnum::PLANT_YOUNG)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($plantYoung);

        $eurekaMoment = new ChargeStatusConfig();
        $eurekaMoment
            ->setName(PlayerStatusEnum::EUREKA_MOMENT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($eurekaMoment);

        $firstTime = new ChargeStatusConfig();
        $firstTime
            ->setName(PlayerStatusEnum::FIRST_TIME)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($firstTime);

        $spores = new ChargeStatusConfig();
        $spores
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($spores);

        $mushStatus = new ChargeStatusConfig();
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
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
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($combustionChamber);

        $drug_eaten = new ChargeStatusConfig();
        $drug_eaten
            ->setName(PlayerStatusEnum::DRUG_EATEN)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($drug_eaten);

        $manager->flush();

        $this->addReference(self::FIRE_STATUS, $fireStatus);
        $this->addReference(self::TURRET_CHARGE, $electricCharge);
        $this->addReference(self::DAILY_ELECTRIC_CHARGE, $dailyElectricCharge);
        $this->addReference(self::CYCLE_ELECTRIC_CHARGE, $electricCharge);
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
