<?php

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class StatusModifierConfigFixtures extends Fixture
{
    public const FROZEN_MODIFIER = 'frozen_modifier';
    public const DISABLED_MODIFIER = 'disabled_modifier';
    public const PACIFIST_MODIFIER = 'pacifist_modifier';

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

        $disabledModifier = new ModifierConfig();
        $disabledModifier
            ->setScope(ModifierScopeEnum::EVENT_ACTION_MOVEMENT_CONVERSION)
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(-2)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($disabledModifier);

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

        $this->addReference(self::FROZEN_MODIFIER, $frozenModifier);
        $this->addReference(self::DISABLED_MODIFIER, $disabledModifier);
        $this->addReference(self::PACIFIST_MODIFIER, $pacifistModifier);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
