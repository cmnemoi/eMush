<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\DataFixtures\StatusModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

class ChargeStatusFixtures extends Fixture implements DependentFixtureInterface
{
    public const SCOOTER_CHARGE = 'scooter_charge';
    public const MICROWAVE_CHARGE = 'microwave_charge';
    public const BLASTER_CHARGE = 'blaster_charge';
    public const OLDFAITHFUL_CHARGE = 'oldFaithful_charge';
    public const BIG_WEAPON_CHARGE = 'big_weapon_charge';
    public const COFFEE_CHARGE = 'coffee_machine_charge';
    public const DISPENSER_CHARGE = 'dispenser_charge';
    public const TURRET_CHARGE = 'turret_charge';
    public const PATROLLER_CHARGE = 'patroller_charge';

    public const FIRE_STATUS = 'fire_status';
    public const PLANT_YOUNG = 'plant_young';
    public const EUREKA_MOMENT = 'eureka_moment';
    public const FIRST_TIME = 'first_time';
    public const SPORES = 'spores';
    public const MUSH_STATUS = 'mush_status';
    public const CONTAMINATED_FOOD = 'contaminated_food';
    public const COMBUSTION_CHAMBER = 'combustion_chamber';
    public const DRUG_EATEN_STATUS = 'drug_eaten_status';
    public const DID_THE_THING_STATUS = 'did_the_thing_status';
    public const DID_BORING_SPEECH_STATUS = 'did_boring_speech_status';
    public const ALREADY_WASHED_IN_THE_SINK = 'already_washed_in_the_sink';

    public const UPDATING_TRACKIE_STATUS = 'updating_trackie_status';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(0)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($attemptConfig);

        $microwaveCharge = new ChargeStatusConfig();
        $microwaveCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(4)
            ->setStartCharge(1)
            ->setDischargeStrategy(ActionEnum::EXPRESS_COOK)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($microwaveCharge);

        $scooterCharge = new ChargeStatusConfig();
        $scooterCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(8)
            ->setStartCharge(2)
            ->setDischargeStrategy(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($scooterCharge);

        $blasterCharge = new ChargeStatusConfig();
        $blasterCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(3)
            ->setStartCharge(1)
            ->setDischargeStrategy(ActionEnum::SHOOT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($blasterCharge);

        $oldFaithfulCharge = new ChargeStatusConfig();
        $oldFaithfulCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(12)
            ->setStartCharge(12)
            ->setDischargeStrategy(ActionEnum::SHOOT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($oldFaithfulCharge);

        $bigWeaponCharge = new ChargeStatusConfig();
        $bigWeaponCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategy(ActionEnum::SHOOT)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($bigWeaponCharge);

        $dispenserCharge = new ChargeStatusConfig();
        $dispenserCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategy(ActionEnum::DISPENSE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($dispenserCharge);

        $coffeeCharge = new ChargeStatusConfig();
        $coffeeCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setDischargeStrategy(ActionEnum::COFFEE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($coffeeCharge);

        $turretCharge = new ChargeStatusConfig();
        $turretCharge
            ->setName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setMaxCharge(4)
            ->setStartCharge(4)
            ->setDischargeStrategy(ActionEnum::SHOOT_HUNTER)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($turretCharge);

        $fireStatus = new ChargeStatusConfig();
        $fireStatus
            ->setName(StatusEnum::FIRE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setGameConfig($gameConfig)
        ;

        $manager->persist($fireStatus);

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
            ->setStartCharge(1)
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

        /** @var ModifierConfig $showerModifier */
        $showerModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_SHOWER_MODIFIER);
        /** @var ModifierConfig $consumeSatietyModifier */
        $consumeSatietyModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_SATIETY_MODIFIER);
        /** @var ModifierConfig $consumeActionModifier */
        $consumeActionModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_ACTION_MODIFIER);
        /** @var ModifierConfig $consumeMovementModifier */
        $consumeMovementModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_MOVEMENT_MODIFIER);
        /** @var ModifierConfig $consumeHealthModifier */
        $consumeHealthModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_HEALTH_MODIFIER);
        /** @var ModifierConfig $consumeMoralModifier */
        $consumeMoralModifier = $this->getReference(StatusModifierConfigFixtures::MUSH_CONSUME_MORAL_MODIFIER);

        $mushStatus = new ChargeStatusConfig();
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setModifierConfigs(new ArrayCollection([
                $showerModifier,
                $consumeActionModifier,
                $consumeHealthModifier,
                $consumeMoralModifier,
                $consumeMovementModifier,
                $consumeSatietyModifier,
            ]))
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
            ->setStartCharge(2)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($drug_eaten);

        $did_the_thing = new ChargeStatusConfig();
        $did_the_thing
            ->setName(PlayerStatusEnum::DID_THE_THING)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($did_the_thing);

        $did_boring_speech = new ChargeStatusConfig();
        $did_boring_speech
            ->setName(PlayerStatusEnum::DID_BORING_SPEECH)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($did_boring_speech);

        $already_washed_in_the_sink = new ChargeStatusConfig();
        $already_washed_in_the_sink
            ->setName(PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($did_boring_speech);

        $updatingTrackie = new ChargeStatusConfig();
        $updatingTrackie
            ->setName(EquipmentStatusEnum::UPDATING)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setStartCharge(4)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($updatingTrackie);

        $manager->flush();

        $this->addReference(self::SCOOTER_CHARGE, $scooterCharge);
        $this->addReference(self::OLDFAITHFUL_CHARGE, $oldFaithfulCharge);
        $this->addReference(self::BIG_WEAPON_CHARGE, $bigWeaponCharge);
        $this->addReference(self::TURRET_CHARGE, $turretCharge);
        $this->addReference(self::MICROWAVE_CHARGE, $microwaveCharge);
        $this->addReference(self::COFFEE_CHARGE, $coffeeCharge);
        $this->addReference(self::DISPENSER_CHARGE, $dispenserCharge);
        $this->addReference(self::BLASTER_CHARGE, $blasterCharge);

        $this->addReference(self::FIRE_STATUS, $fireStatus);
        $this->addReference(self::PLANT_YOUNG, $plantYoung);
        $this->addReference(self::EUREKA_MOMENT, $eurekaMoment);
        $this->addReference(self::FIRST_TIME, $firstTime);
        $this->addReference(self::SPORES, $spores);
        $this->addReference(self::MUSH_STATUS, $mushStatus);
        $this->addReference(self::CONTAMINATED_FOOD, $contaminated);
        $this->addReference(self::COMBUSTION_CHAMBER, $combustionChamber);
        $this->addReference(self::DRUG_EATEN_STATUS, $drug_eaten);
        $this->addReference(self::DID_THE_THING_STATUS, $did_the_thing);
        $this->addReference(self::DID_BORING_SPEECH_STATUS, $did_boring_speech);
        $this->addReference(self::UPDATING_TRACKIE_STATUS, $updatingTrackie);
        $this->addReference(self::ALREADY_WASHED_IN_THE_SINK, $already_washed_in_the_sink);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            StatusModifierConfigFixtures::class,
        ];
    }
}
