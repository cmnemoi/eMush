<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Condition\CycleEvenModifierCondition;
use Mush\Modifier\Entity\Condition\EquipmentRemainChargesModifierCondition;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Event\ResourcePointChangeEvent;
use Mush\Status\Enum\PlayerStatusEnum;

class GearModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const APRON_MODIFIER = 'apron_modifier';
    public const ARMOR_MODIFIER = 'armor_modifier';
    public const WRENCH_MODIFIER = 'wrench_modifier';
    public const GLOVES_MODIFIER = 'gloves_modifier';
    public const SOAP_MODIFIER = 'soap_modifier';
    public const AIM_MODIFIER = 'aim_modifier';
    public const SCOOTER_MODIFIER = 'scooter_modifier';
    public const ROLLING_BOULDER = 'rolling_boulder';
    public const OSCILLOSCOPE_SUCCESS_MODIFIER = 'oscilloscope_success_modifier';
    public const OSCILLOSCOPE_REPAIR_MODIFIER = 'oscilloscope_repair_modifier';
    public const ANTENNA_MODIFIER = 'antenna_modifier';
    public const GRAVITY_CONVERSION_MODIFIER = 'gravity_conversion_modifier';
    public const GRAVITY_CYCLE_MODIFIER = 'gravity_cycle_modifier';
    public const OXYGEN_TANK_MODIFIER = 'oxygen_tank_modifier';

    public function load(ObjectManager $manager): void
    {
        $apronModifier = new ModifierConfig(
            ModifierNameEnum::APRON_MODIFIER,
            ModifierReachEnum::PLAYER,
            0,
            ModifierModeEnum::SET_VALUE
        );
        $apronModifier
            ->addTargetEvent(EnhancePercentageRollEvent::DIRTY_ROLL_RATE)
            ->setLogKeyWhenApplied(ModifierNameEnum::APRON_MODIFIER);
        $manager->persist($apronModifier);

        $armorModifier = new ModifierConfig(
            ModifierNameEnum::ARMOR_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::HEALTH_POINT
        );
        foreach (ActionTypeEnum::getAgressiveActions() as $action) {
            $armorModifier
                ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [$action]);
        }
        $manager->persist($armorModifier);

        $wrenchModifier = new ModifierConfig(
            ModifierNameEnum::WRENCH_MODIFIER,
            ModifierReachEnum::PLAYER,
            1.5,
            ModifierModeEnum::MULTIPLICATIVE
        );
        foreach (ActionTypeEnum::getTechnicianActions() as $action) {
            $wrenchModifier
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
        $manager->persist($wrenchModifier);

        $glovesModifier = new ModifierConfig(
            ModifierNameEnum::GLOVES_MODIFIER,
            ModifierReachEnum::PLAYER,
            0.2,
            ModifierModeEnum::MULTIPLICATIVE
        );
        $glovesModifier
            ->addTargetEvent(EnhancePercentageRollEvent::CLUMSINESS_ROLL_RATE);
        $manager->persist($glovesModifier);

        $soapModifier = new ModifierConfig(
            ModifierNameEnum::SOAP_MODIFIER,
            ModifierReachEnum::PLAYER,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        $soapModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::SHOWER])
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, [ActionEnum::WASH_IN_SINK]);
        $manager->persist($soapModifier);

        $aimModifier = new ModifierConfig(
            ModifierNameEnum::AIM_MODIFIER,
            ModifierReachEnum::PLAYER,
            1.1,
            ModifierModeEnum::MULTIPLICATIVE,
        );
        foreach (ActionTypeEnum::getShootActions() as $action) {
            $aimModifier
                ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [$action]);
        }
        $manager->persist($aimModifier);

        $antiGravScooterRemainChargeCondition = new EquipmentRemainChargesModifierCondition(GearItemEnum::ANTIGRAV_SCOOTER);
        $manager->persist($antiGravScooterRemainChargeCondition);

        $antiGravScooterModifier = new ModifierConfig(
            ModifierNameEnum::GRAV_SCOOTER_MODIFIER,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $antiGravScooterModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN)
            ->addCondition($antiGravScooterRemainChargeCondition);
        $manager->persist($antiGravScooterModifier);

        $evenCyclesCondition = new CycleEvenModifierCondition();
        $manager->persist($evenCyclesCondition);

        $rollingBoulderModifier = new ModifierConfig(
            ModifierNameEnum::ROLLING_BOULDER_MODIFIER,
            ModifierReachEnum::PLAYER,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $rollingBoulderModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN)
            ->addCondition($evenCyclesCondition);
        $manager->persist($rollingBoulderModifier);

        $oscilloscopeSuccessModifier = new ModifierConfig(
            ModifierNameEnum::OSCILLOSCOPE_SUCCESS_MODIFIER,
            ModifierReachEnum::PLAYER,
            1.5,
            ModifierModeEnum::MULTIPLICATIVE,
        );
        $oscilloscopeSuccessModifier
            ->addTargetEvent(PreparePercentageRollEvent::ACTION_ROLL_RATE, [ActionEnum::STRENGTHEN_HULL]);
        $manager->persist($oscilloscopeSuccessModifier);

        $oscilloscopeRepairModifier = new ModifierConfig(
            ModifierNameEnum::OSCILLOSCOPE_REPAIR_MODIFIER,
            ModifierReachEnum::PLAYER,
            2,
            ModifierModeEnum::MULTIPLICATIVE,
            DaedalusVariableEnum::HULL
        );
        $oscilloscopeRepairModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [ActionEnum::STRENGTHEN_HULL]);
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new ModifierConfig(
            ModifierNameEnum::ANTENNA_MODIFIER,
            ModifierReachEnum::DAEDALUS,
            -1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::ACTION_POINT
        );
        // @TODO communication action
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new ModifierConfig(
            ModifierNameEnum::GRAVITY_CONVERSION_MODIFIER,
            ModifierReachEnum::DAEDALUS,
            2,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $gravityConversionModifier
            ->addTargetEvent(ResourcePointChangeEvent::CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN);
            // @TODO IF BROKEN ON THE ACTUAL CYCLE, DOESN'T WORK
        $manager->persist($gravityConversionModifier);

        $gravityCycleModifier = new ModifierConfig(
            ModifierNameEnum::GRAVITY_CYCLE_MODIFIER,
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            PlayerVariableEnum::MOVEMENT_POINT
        );
        $gravityCycleModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
            // @TODO IF BROKEN ON THE ACTUAL CYCLE, DOESN'T WORK
        $manager->persist($gravityCycleModifier);

        $oxygenTankModifier = new ModifierConfig(
            ModifierNameEnum::OXYGEN_TANK_MODIFIER,
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            DaedalusVariableEnum::OXYGEN
        );
        $oxygenTankModifier
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $manager->persist($oxygenTankModifier);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::AIM_MODIFIER, $aimModifier);
        $this->addReference(self::SCOOTER_MODIFIER, $antiGravScooterModifier);
        $this->addReference(self::ROLLING_BOULDER, $rollingBoulderModifier);
        $this->addReference(self::OSCILLOSCOPE_SUCCESS_MODIFIER, $oscilloscopeSuccessModifier);
        $this->addReference(self::OSCILLOSCOPE_REPAIR_MODIFIER, $oscilloscopeRepairModifier);
        $this->addReference(self::ANTENNA_MODIFIER, $antennaModifier);
        $this->addReference(self::GRAVITY_CONVERSION_MODIFIER, $gravityConversionModifier);
        $this->addReference(self::GRAVITY_CYCLE_MODIFIER, $gravityCycleModifier);
        $this->addReference(self::OXYGEN_TANK_MODIFIER, $oxygenTankModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
