<?php

namespace Mush\Player\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;

class CharacterConfigData
{
    public static array $commonActions = [
        'hit',
        'hide',
        'search',
        'heal',
        'self_heal',
        'flirt',
        'do_the_thing',
        'auto_destroy',
        'suicide',
        'kill_player',
        'rejuvenate_alpha',
        'fake_disease',
        'trigger_all_rebel_contacts',
        'create_trade',
        ActionEnum::ACCEPT_MISSION->value,
        ActionEnum::REJECT_MISSION->value,
        ActionEnum::COMMANDER_ORDER->value,
        ActionEnum::RESET_SKILL_POINTS->value,
        ActionEnum::GUARD->value,
        ActionEnum::REPORT_FIRE->value,
        ActionEnum::COM_MANAGER_ANNOUNCEMENT->value,
    ];

    public static array $dataArray = [
        [
            'name' => 'andie',
            'characterName' => 'andie',
            'skillConfigs' => [
                SkillEnum::PILOT,
                SkillEnum::DEVOTION,
                SkillEnum::POLYVALENT,
                SkillEnum::CONFIDENT,
                SkillEnum::EXPERT,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'chao',
            'characterName' => 'chao',
            'skillConfigs' => [
                SkillEnum::SHOOTER,
                SkillEnum::SURVIVALIST,
                SkillEnum::WRESTLER,
                SkillEnum::TORTURER,
                SkillEnum::CRAZY_EYE,
                SkillEnum::INTIMIDATING,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'hyperactive_default',
            ],
        ],
        [
            'name' => 'chun',
            'characterName' => 'chun',
            'skillConfigs' => [
                SkillEnum::MANKIND_ONLY_HOPE,
                SkillEnum::NURSE,
                SkillEnum::PRESENTIMENT,
                SkillEnum::SNEAK,
                SkillEnum::LETHARGY,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'immunized_default',
            ],
        ],
        [
            'name' => 'derek',
            'characterName' => 'derek',
            'skillConfigs' => [
                SkillEnum::SHOOTER,
                SkillEnum::WRESTLER,
                SkillEnum::FIREFIGHTER,
                SkillEnum::HYGIENIST,
                SkillEnum::MOTIVATOR,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'first_time_default',
            ],
        ],
        [
            'name' => 'eleesha',
            'characterName' => 'eleesha',
            'skillConfigs' => [
                SkillEnum::TRACKER,
                SkillEnum::DETERMINED,
                SkillEnum::OBSERVANT,
                SkillEnum::TECHNICIAN,
                SkillEnum::IT_EXPERT,
                SkillEnum::POLYMATH,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [
                'chronic_vertigo_default',
            ],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'finola',
            'characterName' => 'finola',
            'skillConfigs' => [
                SkillEnum::BIOLOGIST,
                SkillEnum::MEDIC,
                SkillEnum::NURSE,
                SkillEnum::DIPLOMAT,
                SkillEnum::OCD,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'germaphobe_default',
            ],
        ],
        [
            'name' => 'frieda',
            'characterName' => 'frieda',
            'skillConfigs' => [
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::PILOT,
                SkillEnum::SURVIVALIST,
                SkillEnum::IT_EXPERT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::ANTIQUE_PERFUME,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'gioele',
            'characterName' => 'gioele',
            'skillConfigs' => [
                SkillEnum::SOLID,
                SkillEnum::PARANOID,
                SkillEnum::CAFFEINE_JUNKIE,
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::PANIC,
                SkillEnum::VICTIMIZER,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'hua',
            'characterName' => 'hua',
            'skillConfigs' => [
                SkillEnum::PILOT,
                SkillEnum::BOTANIST,
                SkillEnum::SURVIVALIST,
                SkillEnum::TECHNICIAN,
                SkillEnum::DETERMINED,
                SkillEnum::U_TURN,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'ian',
            'characterName' => 'ian',
            'skillConfigs' => [
                SkillEnum::BOTANIST,
                SkillEnum::BIOLOGIST,
                SkillEnum::MYCOLOGIST,
                SkillEnum::FIREFIGHTER,
                SkillEnum::GREEN_THUMB,
                SkillEnum::FRUGIVORE,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'pacifist_default',
            ],
        ],
        [
            'name' => 'janice',
            'characterName' => 'janice',
            'skillConfigs' => [
                SkillEnum::SHRINK,
                SkillEnum::IT_EXPERT,
                SkillEnum::NERON_ONLY_FRIEND,
                SkillEnum::DIPLOMAT,
                SkillEnum::RADIO_EXPERT,
                SkillEnum::SELF_SACRIFICE,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'jin_su',
            'characterName' => 'jin_su',
            'skillConfigs' => [
                SkillEnum::LEADER,
                SkillEnum::PILOT,
                SkillEnum::COLD_BLOODED,
                SkillEnum::SHOOTER,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::STRATEGURU,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'kuan_ti',
            'characterName' => 'kuan_ti',
            'skillConfigs' => [
                SkillEnum::CONCEPTOR,
                SkillEnum::OPTIMIST,
                SkillEnum::ASTROPHYSICIST,
                SkillEnum::TECHNICIAN,
                SkillEnum::LEADER,
                SkillEnum::POLITICIAN,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'paola',
            'characterName' => 'paola',
            'skillConfigs' => [
                SkillEnum::RADIO_EXPERT,
                SkillEnum::LOGISTICS_EXPERT,
                SkillEnum::SHOOTER,
                SkillEnum::BIOLOGIST,
                SkillEnum::REBEL,
                SkillEnum::GUNNER,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'raluca',
            'characterName' => 'raluca',
            'skillConfigs' => [
                SkillEnum::PHYSICIST,
                SkillEnum::DETACHED_CREWMEMBER,
                SkillEnum::TECHNICIAN,
                SkillEnum::GENIUS,
                SkillEnum::CONCEPTOR,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'antisocial_default',
                'cat_owner_default',
            ],
        ],
        [
            'name' => 'roland',
            'characterName' => 'roland',
            'skillConfigs' => [
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::FIREFIGHTER,
                SkillEnum::CREATIVE,
                SkillEnum::OPTIMIST,
                SkillEnum::SPRINTER,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'stephen',
            'characterName' => 'stephen',
            'skillConfigs' => [
                SkillEnum::CHEF,
                SkillEnum::SOLID,
                SkillEnum::SHOOTER,
                SkillEnum::APPRENTICE,
                SkillEnum::CREATIVE,
                SkillEnum::OPPORTUNIST,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [],
        ],
        [
            'name' => 'terrence',
            'characterName' => 'terrence',
            'skillConfigs' => [
                SkillEnum::ROBOTICS_EXPERT,
                SkillEnum::TECHNICIAN,
                SkillEnum::PILOT,
                SkillEnum::SHOOTER,
                SkillEnum::IT_EXPERT,
                SkillEnum::METALWORKER,
            ],
            'maxNumberPrivateChannel' => 3,
            'maxHealthPoint' => 14,
            'maxMoralPoint' => 14,
            'maxActionPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 3,
            'maxDiscoverablePlanets' => 2,
            'initHealthPoint' => 14,
            'initMoralPoint' => 7,
            'initSatiety' => 0,
            'initActionPoint' => 8,
            'initMovementPoint' => 12,
            'actions' => [],
            'initDiseases' => [],
            'startingItems' => [
                'itrackie_default',
            ],
            'initStatuses' => [
                'disabled_default',
            ],
        ],
    ];

    public static function getByName(string $name): array
    {
        $data = current(array_filter(self::$dataArray, static fn (array $data) => $data['name'] === $name));
        if (!$data) {
            throw new \Exception("Character {$name} not found");
        }

        return $data;
    }
}
