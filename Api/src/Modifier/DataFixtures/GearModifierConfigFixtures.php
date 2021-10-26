<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;

class GearModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const APRON_MODIFIER = 'apron_modifier';
    public const ARMOR_MODIFIER = 'armor_modifier';
    public const WRENCH_MODIFIER = 'wrench_modifier';
    public const GLOVES_MODIFIER = 'gloves_modifier';
    public const SOAP_MODIFIER = 'soap_modifier';
    public const AIM_MODIFIER = 'aim_modifier';
    public const SCOOTER_MODIFIER = 'scooter_modifier';
    public const OSCILLOSCOPE_SUCCESS_MODIFIER = 'oscilloscope_success_modifier';
    public const OSCILLOSCOPE_REPAIR_MODIFIER = 'oscilloscope_repair_modifier';
    public const ANTENNA_MODIFIER = 'antenna_modifier';
    public const GRAVITY_CONVERSION_MODIFIER = 'gravity_conversion_modifier';
    public const GRAVITY_CYCLE_MODIFIER = 'gravity_cycle_modifier';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $apronModifier = new ModifierConfig();

        $apronModifier
            ->setScope(ModifierScopeEnum::EVENT_DIRTY)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(-100)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($apronModifier);

        $armorModifier = new ModifierConfig();
        $armorModifier
            ->setScope(ModifierScopeEnum::INJURY)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::TARGET_PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($armorModifier);

        $wrenchModifier = new ModifierConfig();
        $wrenchModifier
            ->setScope(ActionTypeEnum::ACTION_TECHNICIAN)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($wrenchModifier);

        $glovesModifier = new ModifierConfig();
        $glovesModifier
            ->setScope(ModifierScopeEnum::EVENT_CLUMSINESS)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::SET_VALUE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($glovesModifier);

        $soapModifier = new ModifierConfig();
        $soapModifier
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($soapModifier);

        $aimModifier = new ModifierConfig();
        $aimModifier
            ->setScope(ActionTypeEnum::ACTION_SHOOT)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($aimModifier);

        $antiGravScooterModifier = new ModifierConfig();
        $antiGravScooterModifier
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($antiGravScooterModifier);

        $oscilloscopeSuccessModifier = new ModifierConfig();
        $oscilloscopeSuccessModifier
            ->setScope(ActionEnum::STRENGTHEN_HULL)
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($oscilloscopeSuccessModifier);

        $oscilloscopeRepairModifier = new ModifierConfig();
        $oscilloscopeRepairModifier
            ->setScope(DaedalusModifierEvent::CHANGE_HULL)
            ->setTarget(ModifierTargetEnum::HULL)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::MULTIPLICATIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($oscilloscopeRepairModifier);

        $antennaModifier = new ModifierConfig();
        $antennaModifier
            ->setScope('TODO comms. action')
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($antennaModifier);

        $gravityConversionModifier = new ModifierConfig();
        $gravityConversionModifier
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($gravityConversionModifier);

        $gravityCycleModifier = new ModifierConfig();
        $gravityCycleModifier
            ->setScope(ModifierScopeEnum::CYCLE_CHANGE)
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($gravityCycleModifier);

        $manager->flush();

        $this->addReference(self::APRON_MODIFIER, $apronModifier);
        $this->addReference(self::ARMOR_MODIFIER, $armorModifier);
        $this->addReference(self::WRENCH_MODIFIER, $wrenchModifier);
        $this->addReference(self::GLOVES_MODIFIER, $glovesModifier);
        $this->addReference(self::SOAP_MODIFIER, $soapModifier);
        $this->addReference(self::AIM_MODIFIER, $aimModifier);
        $this->addReference(self::SCOOTER_MODIFIER, $antiGravScooterModifier);
        $this->addReference(self::OSCILLOSCOPE_SUCCESS_MODIFIER, $oscilloscopeSuccessModifier);
        $this->addReference(self::OSCILLOSCOPE_REPAIR_MODIFIER, $oscilloscopeRepairModifier);
        $this->addReference(self::ANTENNA_MODIFIER, $antennaModifier);
        $this->addReference(self::GRAVITY_CONVERSION_MODIFIER, $gravityConversionModifier);
        $this->addReference(self::GRAVITY_CYCLE_MODIFIER, $gravityCycleModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
