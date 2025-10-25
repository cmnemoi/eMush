<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;

/** @codeCoverageIgnore */
class EventConfigFixtures extends Fixture
{
    public const string MAX_HEALTH_REDUCE_1 = 'change.value.max_player_-1_healthPoint';
    public const string MAX_HEALTH_REDUCE_2 = 'change.value.max_player_-2_healthPoint';
    public const string MAX_HEALTH_REDUCE_4 = 'change.value.max_player_-4_healthPoint';
    public const string MAX_MORAL_REDUCE_1 = 'change.value.max_player_-1_moralPoint';
    public const string MAX_MORAL_REDUCE_2 = 'change.value.max_player_-2_moralPoint';
    public const string MAX_MORAL_REDUCE_3 = 'change.value.max_player_-3_moralPoint';
    public const string MAX_MORAL_REDUCE_4 = 'change.value.max_player_-4_moralPoint';
    public const string MAX_ACTION_REDUCE_2 = 'change.value.max_player_-2_actionPoint';
    public const string MAX_MOVEMENT_REDUCE_3 = 'change.value.max_player_-3_movementPoint';
    public const string MAX_MOVEMENT_REDUCE_5 = 'change.value.max_player_-5_movementPoint';
    public const string MAX_MOVEMENT_REDUCE_12 = 'change.value.max_player_-12_movementPoint';
    public const string HEALTH_REDUCE_1 = 'change.variable_player_-1_healthPoint';
    public const string HEALTH_REDUCE_2 = 'change.variable_player_-2_healthPoint';
    public const string HEALTH_REDUCE_3 = 'change.variable_player_-3_healthPoint';
    public const string HEALTH_REDUCE_4 = 'change.variable_player_-4_healthPoint';
    public const string HEALTH_REDUCE_6 = 'change.variable_player_-6_healthPoint';
    public const string MORAL_REDUCE_1 = 'change.variable_player_-1_moralPoint';
    public const string MORAL_REDUCE_2 = 'change.variable_player_-2_moralPoint';
    public const string ACTION_INCREASE_1 = 'change.variable_player_1_actionPoint';
    public const string ACTION_REDUCE_1 = 'change.variable_player_-1_actionPoint';
    public const string ACTION_REDUCE_2 = 'change.variable_player_-2_actionPoint';
    public const string MOVEMENT_REDUCE_1 = 'change.variable_player_-1_movementPoint';
    public const string MOVEMENT_REDUCE_2 = 'change.variable_player_-2_movementPoint';
    public const string MOVEMENT_INCREASE_1 = 'change.variable_player_1_movementPoint';
    public const string SATIETY_REDUCE_1 = 'change.variable_player_-1_satiety';
    public const string SATIETY_INCREASE_1 = 'change.variable_player_1_satiety';

    public function load(ObjectManager $manager): void
    {
        // TODO Replace constructor to be able to merge all those for loops!
        foreach (EventConfigData::$variableEventConfigData as $rawEventConfig) {
            $eventConfig = new VariableEventConfig($rawEventConfig['name'], $rawEventConfig['eventName']);
            $eventConfig
                ->setQuantity($rawEventConfig['quantity'])
                ->setTargetVariable($rawEventConfig['targetVariable'])
                ->setVariableHolderClass($rawEventConfig['variableHolderClass']);

            $this->addReference($eventConfig->getName(), $eventConfig);
            $manager->persist($eventConfig);
        }
        $manager->flush();
    }
}
