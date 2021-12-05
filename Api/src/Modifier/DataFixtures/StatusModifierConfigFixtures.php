<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class StatusModifierConfigFixtures extends Fixture
{
    public const FROZEN_MODIFIER = 'frozen_modifier';
    public const DISABLED_CONVERSION_MODIFIER = 'disabled_conversion_modifier';
    public const DISABLED_NOT_ALONE_MODIFIER = 'disabled_not_alone_modifier';
    public const PACIFIST_MODIFIER = 'pacifist_modifier';
    public const BURDENED_MODIFIER = 'burdened_modifier';
    public const ANTISOCIAL_MODIFIER = 'antisocial_modifier';
    public const LOST_MODIFIER = 'lost_modifier';
    public const LYING_DOWN_MODIFIER = 'lying_down_modifier';
    public const STARVING_MODIFIER = 'starving_modifier';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $frozenModifier = new ModifierConfig();

        $frozenModifier
            ->setScope(ActionEnum::CONSUME)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::EQUIPMENT)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($frozenModifier);

        $disabledConversionModifier = new ModifierConfig();
        $disabledConversionModifier
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($disabledConversionModifier);

        $notAloneCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_IN_ROOM);
        $notAloneCondition->setCondition(ModifierConditionEnum::NOT_ALONE);
        $manager->persist($notAloneCondition);

        $disabledNotAloneModifier = new ModifierConfig();
        $disabledNotAloneModifier
            ->setScope(ActionEnum::MOVE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notAloneCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($disabledNotAloneModifier);

        $pacifistModifier = new ModifierConfig();
        $pacifistModifier
            ->setScope(ActionTypeEnum::ACTION_AGGRESSIVE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($pacifistModifier);

        $burdenedModifier = new ModifierConfig();
        $burdenedModifier
            ->setScope(ActionEnum::MOVE)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($burdenedModifier);

        $antisocialModifier = new ModifierConfig();
        $antisocialModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notAloneCondition)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($antisocialModifier);

        $lostModifier = new ModifierConfig();
        $lostModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($lostModifier);

        $lyingDownModifier = new ModifierConfig();
        $lyingDownModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($lyingDownModifier);

        $starvingModifier = new ModifierConfig();
        $starvingModifier
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::HEALTH_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($starvingModifier);

        $manager->flush();

        $this->addReference(self::FROZEN_MODIFIER, $frozenModifier);
        $this->addReference(self::DISABLED_CONVERSION_MODIFIER, $disabledConversionModifier);
        $this->addReference(self::DISABLED_NOT_ALONE_MODIFIER, $disabledNotAloneModifier);
        $this->addReference(self::PACIFIST_MODIFIER, $pacifistModifier);
        $this->addReference(self::BURDENED_MODIFIER, $burdenedModifier);
        $this->addReference(self::ANTISOCIAL_MODIFIER, $antisocialModifier);
        $this->addReference(self::LOST_MODIFIER, $lostModifier);
        $this->addReference(self::LYING_DOWN_MODIFIER, $lyingDownModifier);
        $this->addReference(self::STARVING_MODIFIER, $starvingModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
