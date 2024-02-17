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
        foreach (EventConfigData::$dataArray as $rawEventConfig) {
            switch ($rawEventConfig['type']) {
                case 'variable_event_config':
                    $eventConfig = new VariableEventConfig();
                    $eventConfig
                        ->setQuantity($rawEventConfig['quantity'])
                        ->setTargetVariable($rawEventConfig['targetVariable'])
                        ->setVariableHolderClass($rawEventConfig['variableHolderClass'])
                    ;
                    break;
                case 'planet_sector_event_config':
                    $eventConfig = new PlanetSectorEventConfig();
                    $eventConfig
                        ->setOutputTable($rawEventConfig['outputTable'])
                        ->setOutputQuantity($rawEventConfig['outputQuantity'])
                    ;

                    break;
                default:
                    throw new \Exception('Unknown event config type');
            }

            $eventConfig
                ->setEventName($rawEventConfig['eventName'])
                ->setName($rawEventConfig['name'])
            ;

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
