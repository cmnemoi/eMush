<?php

namespace Mush\Game\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
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
        foreach (EventConfigData::getAllEventConfig() as $rawEventConfig) {
            switch ($rawEventConfig['type']) {
                case 'variable_event_config':
                    $eventConfig = new VariableEventConfig();
                    $eventConfig
                        ->setQuantity($rawEventConfig['quantity'])
                        ->setTargetVariable($rawEventConfig['targetVariable'])
                        ->setVariableHolderClass($rawEventConfig['variableHolderClass']);

                    break;

                case 'planet_sector_event_config':
                    $eventConfig = new PlanetSectorEventConfig();
                    $eventConfig
                        ->setOutputTable($rawEventConfig['outputTable'])
                        ->setOutputQuantity($rawEventConfig['outputQuantity']);

                    break;

                default:
                    throw new \Exception('Unknown event config type');
            }

            $eventConfig
                ->setEventName($rawEventConfig['eventName'])
                ->setName($rawEventConfig['name']);

            $this->addReference($eventConfig->getName(), $eventConfig);

            $manager->persist($eventConfig);
        }

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig->setEventName('fight');
        $eventConfig->setName('fight_1');
        $eventConfig->setOutputQuantity([]);
        $eventConfig->setOutputTable([1 => 1]);
        $this->addReference($eventConfig->getName(), $eventConfig);
        $manager->persist($eventConfig);

        $eventConfig = new PlanetSectorEventConfig();
        $eventConfig->setEventName('fight');
        $eventConfig->setName('fight_2');
        $eventConfig->setOutputQuantity([]);
        $eventConfig->setOutputTable([2 => 1]);
        $this->addReference($eventConfig->getName(), $eventConfig);
        $manager->persist($eventConfig);

        $manager->flush();
    }
}
