<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

class ActionLogEnum
{
    public const DISASSEMBLE_SUCCESS = 'disassemble_success';
    public const DISASSEMBLE_FAIL = 'disassemble_fail';
    public const BUILD_SUCCESS = 'build_success';
    public const COFFEE_SUCCESS = 'coffee_success';
    public const COMFORT_SUCCESS = 'comfort_success';
    public const CONSUME_SUCCESS = 'consume_success';
    public const CONSUME_DRUG = 'consume_drug';
    public const COOK_SUCCESS = 'cook_success';
    public const DISPENSE_SUCCESS = 'dispense_success';
    public const DO_THE_THING_SUCCESS = 'do_the_thing_success';
    public const DROP = 'drop';
    public const EXPRESS_COOK_SUCCESS = 'express_cook_success';
    public const EXTINGUISH_SUCCESS = 'extinguish_success';
    public const EXTINGUISH_FAIL = 'extinguish_fail';
    public const EXTRACT_SPORE_SUCCESS = 'extract_spore_success';
    public const FLIRT_SUCCESS = 'flirt_success';
    public const GET_UP = 'get_up';
    public const HEAL_SUCCESS = 'heal_success';
    public const HIDE_SUCCESS = 'hide_success';
    public const HIT_SUCCESS = 'hit_success';
    public const HIT_FAIL = 'hit_fail';
    public const HYBRIDIZE_SUCCESS = 'hybridize_success';
    public const HYBRIDIZE_FAIL = 'transplant_fail';
    public const HYPERFREEZE_SUCCESS = 'hyperfreeze_success';
    public const INFECT_SUCCESS = 'infect_success';
    public const INSERT_FUEL = 'insert_fuel';
    public const INSERT_OXYGEN = 'insert_oxygen';
    public const RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const RETRIEVE_FUEL = 'retrieve_fuel';
    public const LIE_DOWN = 'lie_down';
    public const EXIT_ROOM = 'exit_room';
    public const ENTER_ROOM = 'enter_room';
    public const READ_BOOK = 'read_book';
    public const READ_DOCUMENT = 'read_document';
    public const REPAIR_SUCCESS = 'repair_success';
    public const REPAIR_FAIL = 'repair_fail';
    public const SABOTAGE_SUCCESS = 'sabotage_success';
    public const SABOTAGE_FAIL = 'sabotage_fail';
    public const SEARCH_SUCCESS = 'search_success';
    public const SEARCH_FAIL = 'search_fail';
    public const SHRED_SUCCESS = 'shred_success';
    public const SHOWER_HUMAN = 'shower_human';
    public const SHOWER_MUSH = 'shower_mush';
    public const STRENGTHEN_SUCCESS = 'strengthen_success';
    public const SPREAD_FIRE_SUCCESS = 'spread_fire_success';
    public const TAKE = 'take';
    public const TRANSPLANT_SUCCESS = 'transplant_success';
    public const TREAT_PLANT_SUCCESS = 'treat_plant_success';
    public const TRY_KUBE = 'try_kube';
    public const ULTRAHEAL_SUCCESS = 'ultraheal_success';
    public const SELF_HEAL = 'self_heal';
    public const WATER_PLANT_SUCCESS = 'water_plant_success';
    public const WRITE_SUCCESS = 'write_success';
    public const OPEN_SUCCESS = 'open_success';
    public const INSTALL_CAMERA = 'install_camera';
    public const REMOVE_CAMERA = 'remove_camera';
    public const CHECK_SPORE_LEVEL = 'check_spore_level';
    public const REMOVE_SPORE_SUCCESS = 'remove_spore_success';
    public const REMOVE_SPORE_FAIL = 'remove_spore_fail';
    public const PUBLIC_BROADCAST = 'public_broadcast';
    public const MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const BORING_SPEECH = 'boring_speech';
    public const MAKE_SICK = 'make_sick';
    public const FAKE_DISEASE = 'fake_disease';
    public const FAIL_SURGERY = 'fail_surgery';
    public const FAIL_SELF_SURGERY = 'fail_self_surgery';
    public const UPDATE_TALKIE_SUCCESS = 'update_talkie_success';
    public const SCREW_TALKIE_SUCCESS = 'screw_talkie_success';

    public const DEFAULT_FAIL = 'default_fail';

    public const VISIBILITY = 'visibility';
    public const VALUE = 'value';

    public const ACTION_LOGS = [
        ActionEnum::DISASSEMBLE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::DISASSEMBLE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::DISASSEMBLE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TAKE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::TAKE,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HIDE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::HIDE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::DROP => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::DROP,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::REPAIR => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::REPAIR_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::REPAIR_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::SEARCH => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SEARCH_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::SEARCH_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::EXTRACT_SPORE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::EXTRACT_SPORE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::INFECT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::INFECT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::SABOTAGE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SABOTAGE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::SABOTAGE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::READ_DOCUMENT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::READ_DOCUMENT,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::READ_BOOK => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::READ_BOOK,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SHRED => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SHRED_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::CONSUME => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::CONSUME_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::CONSUME_DRUG => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::CONSUME_DRUG,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::WATER_PLANT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::WATER_PLANT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TREAT_PLANT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::TREAT_PLANT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TRY_KUBE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::TRY_KUBE,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HYBRIDIZE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::HYBRIDIZE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::HYBRIDIZE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::EXTINGUISH => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::EXTINGUISH_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::EXTINGUISH_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::HYPERFREEZE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::HYPERFREEZE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::EXPRESS_COOK => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::COOK_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::WRITE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::WRITE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::INSERT_OXYGEN => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::INSERT_OXYGEN,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::RETRIEVE_OXYGEN => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::RETRIEVE_OXYGEN,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::INSERT_FUEL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::INSERT_FUEL,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::RETRIEVE_FUEL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::RETRIEVE_FUEL,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::COOK => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::COOK_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::COFFEE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::COFFEE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::DISPENSE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::DISPENSE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SHOWER => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SHOWER_HUMAN,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::SHOWER_MUSH,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::LIE_DOWN => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::LIE_DOWN,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::GET_UP => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::GET_UP,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HIT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::HIT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::HIT_FAIL,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::COMFORT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::COMFORT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HEAL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::HEAL_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SELF_HEAL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SELF_HEAL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::ULTRAHEAL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::ULTRAHEAL_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::USE_BANDAGE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SELF_HEAL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::SPREAD_FIRE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SPREAD_FIRE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::INSTALL_CAMERA => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::INSTALL_CAMERA,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::REMOVE_CAMERA => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::REMOVE_CAMERA,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],

        ActionEnum::STRENGTHEN_HULL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::STRENGTHEN_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::DEFAULT_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],

        ActionEnum::FLIRT => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::FLIRT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],

        ActionEnum::MOVE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::ENTER_ROOM,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],

        ActionEnum::DO_THE_THING => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::DO_THE_THING_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],

        ActionEnum::CHECK_SPORE_LEVEL => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::CHECK_SPORE_LEVEL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],

        ActionEnum::REMOVE_SPORE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::REMOVE_SPORE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::REMOVE_SPORE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::PUBLIC_BROADCAST => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::PUBLIC_BROADCAST,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::EXTINGUISH_MANUALLY => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::EXTINGUISH_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            ActionOutputEnum::FAIL => [
                self::VALUE => self::EXTINGUISH_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::MOTIVATIONAL_SPEECH => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::MOTIVATIONAL_SPEECH,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::BORING_SPEECH => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::BORING_SPEECH,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::MAKE_SICK => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::MAKE_SICK,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::FAKE_DISEASE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::FAKE_DISEASE,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::SURGERY => [
            ActionOutputEnum::FAIL => [
                self::VALUE => self::FAIL_SURGERY,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SELF_SURGERY => [
            ActionOutputEnum::FAIL => [
                self::VALUE => self::FAIL_SELF_SURGERY,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SCREW_TALKIE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::SCREW_TALKIE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::UPDATE_TALKIE => [
            ActionOutputEnum::SUCCESS => [
                self::VALUE => self::UPDATE_TALKIE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
    ];
}
