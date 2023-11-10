<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\PlayerVariableEnum;

/** @codeCoverageIgnore */
class EventConfigFixtures extends Fixture
{
    public const MAX_HEALTH_REDUCE_1 = 'change.value.max_player_-1_healthPoint';
    public const MAX_HEALTH_REDUCE_2 = 'change.value.max_player_-2_healthPoint';
    public const MAX_HEALTH_REDUCE_4 = 'change.value.max_player_-4_healthPoint';
    public const MAX_MORAL_REDUCE_1 = 'change.value.max_player_-1_moralPoint';
    public const MAX_MORAL_REDUCE_2 = 'change.value.max_player_-2_moralPoint';
    public const MAX_MORAL_REDUCE_3 = 'change.value.max_player_-3_moralPoint';
    public const MAX_MORAL_REDUCE_4 = 'change.value.max_player_-4_moralPoint';
    public const MAX_ACTION_REDUCE_2 = 'change.value.max_player_-2_actionPoint';
    public const MAX_MOVEMENT_REDUCE_3 = 'change.value.max_player_-3_movementPoint';
    public const MAX_MOVEMENT_REDUCE_5 = 'change.value.max_player_-5_movementPoint';
    public const MAX_MOVEMENT_REDUCE_12 = 'change.value.max_player_-12_movementPoint';
    public const HEALTH_REDUCE_1 = 'change.variable_player_-1_healthPoint';
    public const HEALTH_REDUCE_2 = 'change.variable_player_-2_healthPoint';
    public const HEALTH_REDUCE_3 = 'change.variable_player_-3_healthPoint';
    public const HEALTH_REDUCE_4 = 'change.variable_player_-4_healthPoint';
    public const HEALTH_REDUCE_6 = 'change.variable_player_-6_healthPoint';
    public const MORAL_REDUCE_1 = 'change.variable_player_-1_moralPoint';
    public const ACTION_INCREASE_1 = 'change.variable_player_1_actionPoint';
    public const ACTION_REDUCE_1 = 'change.variable_player_-1_actionPoint';
    public const ACTION_REDUCE_2 = 'change.variable_player_-2_actionPoint';
    public const MOVEMENT_REDUCE_1 = 'change.variable_player_-1_movementPoint';
    public const MOVEMENT_REDUCE_2 = 'change.variable_player_-2_movementPoint';
    public const MOVEMENT_INCREASE_1 = 'change.variable_player_1_movementPoint';
    public const SATIETY_REDUCE_1 = 'change.variable_player_-1_satiety';
    public const SATIETY_INCREASE_1 = 'change.variable_player_1_satiety';

    public function load(ObjectManager $manager): void
    {
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-1_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_HEALTH_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-2_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_HEALTH_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-4)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-4_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_HEALTH_REDUCE_4, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-1_moralPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MORAL_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-2_moralPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MORAL_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-3)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-3_moralPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MORAL_REDUCE_3, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-4)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-4_moralPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MORAL_REDUCE_4, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-2_actionPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_ACTION_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-3)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-3_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MOVEMENT_REDUCE_3, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-5)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-5_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MOVEMENT_REDUCE_5, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-12)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VALUE_MAX)
            ->setName('change.value.max_player_-12_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MAX_MOVEMENT_REDUCE_12, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-1_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::HEALTH_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-2_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::HEALTH_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-3)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-3_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::HEALTH_REDUCE_3, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-4)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-4_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::HEALTH_REDUCE_4, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-6)
            ->setTargetVariable(PlayerVariableEnum::HEALTH_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-6_healthPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::HEALTH_REDUCE_6, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-1_moralPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MORAL_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(1)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_1_actionPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::ACTION_INCREASE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-1_actionPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::ACTION_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-2_actionPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::ACTION_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-1_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MOVEMENT_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-2)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-2_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MOVEMENT_REDUCE_2, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(1)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_1_movementPoint')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::MOVEMENT_INCREASE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(-1)
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_-1_satiety')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::SATIETY_REDUCE_1, $eventConfig);

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setQuantity(1)
            ->setTargetVariable(PlayerVariableEnum::SATIETY)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
            ->setName('change.variable_player_1_satiety')
        ;
        $manager->persist($eventConfig);
        $this->addReference(self::SATIETY_INCREASE_1, $eventConfig);

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig
            ->setOutputQuantityTable([
                3 => 1,
                4 => 1,
                5 => 1,
            ])
            ->setName(PlanetSectorEvent::DISASTER . '_3_5')
            ->setEventName(PlanetSectorEvent::DISASTER)
        ;
        $manager->persist($eventConfig);

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig
            ->setOutputQuantityTable([
                3 => 1,
                4 => 1,
                5 => 1,
            ])
            ->setName(PlanetSectorEvent::DISASTER . '_3_5')
            ->setEventName(PlanetSectorEvent::DISASTER)

        ;
        $manager->persist($eventConfig);

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig
            ->setName(PlanetSectorEvent::NOTHING_TO_REPORT)
            ->setEventName(PlanetSectorEvent::NOTHING_TO_REPORT)
        ;
        $manager->persist($eventConfig);

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig
            ->setOutputQuantityTable([
                2 => 1,
            ])
            ->setName(PlanetSectorEvent::TIRED . '_2')
            ->setEventName(PlanetSectorEvent::TIRED)
        ;
        $manager->persist($eventConfig);

        $manager->flush();
    }
}
