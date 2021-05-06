<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;

class ActionLogEnum
{
    public const DISASSEMBLE_SUCCESS = 'disassemble_success';
    public const DISASSEMBLE_FAIL = 'disassemble_fail';
    public const BUILD_SUCCESS = 'build_success';
    public const COFFEE_SUCCESS = 'coffee_success';
    public const CONSUME_SUCCESS = 'consume_success';
    public const CONSUME_DRUG = 'consume_drug';
    public const COOK_SUCCESS = 'cook_success';
    public const DISPENSE_SUCCESS = 'dispense_success';
    public const DROP = 'drop';
    public const EXPRESS_COOK_SUCCESS = 'express_cook_success';
    public const EXTINGUISH_SUCCESS = 'extinguish_success';
    public const EXTINGUISH_FAIL = 'extinguish_fail';
    public const EXTRACT_SPORE_SUCCESS = 'extract_spore_success';
    public const GET_UP = 'get_up';
    public const HIDE_SUCCESS = 'hide_success';
    public const HIT_SUCCESS = 'hit_success';
    public const HIT_FAIL = 'hit_fail';
    public const HYPERFREEZE_SUCCESS = 'hyperfreeze_success';
    public const INFECT_SUCCESS = 'infect_success';
    public const INSERT_OXYGEN = 'insert_oxygen';
    public const RETRIEVE_OXYGEN = 'retrieve_oxygen';
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
    public const TAKE = 'take';
    public const TRANSPLANT_SUCCESS = 'transplant_success';
    public const HYBRIDIZE_SUCCESS = 'hybridize_success';
    public const HYBRIDIZE_FAIL = 'transplant_fail';
    public const TREAT_PLANT_SUCCESS = 'treat_plant_success';
    public const WATER_PLANT_SUCCESS = 'water_plant_success';
    public const WRITE_SUCCESS = 'write_success';

    public const SUCCESS = 'success';
    public const FAIL = 'fail';

    public const VISIBILITY = 'visibility';
    public const VALUE = 'value';

    public const ACTION_LOGS = [
        ActionEnum::DISASSEMBLE => [
            self::SUCCESS => [
                self::VALUE => self::DISASSEMBLE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            self::FAIL => [
                self::VALUE => self::DISASSEMBLE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TAKE => [
            self::SUCCESS => [
                self::VALUE => self::TAKE,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HIDE => [
            self::SUCCESS => [
                self::VALUE => self::HIDE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::DROP => [
            self::SUCCESS => [
                self::VALUE => self::DROP,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::REPAIR => [
            self::SUCCESS => [
                self::VALUE => self::REPAIR_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            self::FAIL => [
                self::VALUE => self::REPAIR_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::SEARCH => [
            self::SUCCESS => [
                self::VALUE => self::SEARCH_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            self::FAIL => [
                self::VALUE => self::SEARCH_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::EXTRACT_SPORE => [
            self::SUCCESS => [
                self::VALUE => self::EXTRACT_SPORE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::MUSH,
            ],
        ],
        ActionEnum::INFECT => [
            self::SUCCESS => [
                self::VALUE => self::INFECT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::SECRET,
            ],
        ],
        ActionEnum::SABOTAGE => [
            self::SUCCESS => [
                self::VALUE => self::SABOTAGE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::MUSH,
            ],
            self::FAIL => [
                self::VALUE => self::SABOTAGE_FAIL,
                self::VISIBILITY => VisibilityEnum::MUSH,
            ],
        ],
        ActionEnum::READ_DOCUMENT => [
            self::SUCCESS => [
                self::VALUE => self::READ_DOCUMENT,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::READ_BOOK => [
            self::SUCCESS => [
                self::VALUE => self::READ_BOOK,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SHRED => [
            self::SUCCESS => [
                self::VALUE => self::SHRED_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::CONSUME => [
            self::SUCCESS => [
                self::VALUE => self::CONSUME_SUCCESS,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::CONSUME_DRUG => [
            self::SUCCESS => [
                self::VALUE => self::CONSUME_DRUG,
                self::VISIBILITY => VisibilityEnum::COVERT,
            ],
        ],
        ActionEnum::BUILD => [
            self::SUCCESS => [
                self::VALUE => self::BUILD_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::WATER_PLANT => [
            self::SUCCESS => [
                self::VALUE => self::WATER_PLANT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TREAT_PLANT => [
            self::SUCCESS => [
                self::VALUE => self::TREAT_PLANT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::TRANSPLANT => [
            self::SUCCESS => [
                self::VALUE => self::TRANSPLANT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::HYBRIDIZE => [
            self::SUCCESS => [
                self::VALUE => self::HYBRIDIZE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
            self::FAIL => [
                self::VALUE => self::HYBRIDIZE_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::EXTINGUISH => [
            self::SUCCESS => [
                self::VALUE => self::EXTINGUISH_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            self::FAIL => [
                self::VALUE => self::EXTINGUISH_FAIL,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::HYPERFREEZE => [
            self::SUCCESS => [
                self::VALUE => self::HYPERFREEZE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::EXPRESS_COOK => [
            self::SUCCESS => [
                self::VALUE => self::COOK_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::WRITE => [
            self::SUCCESS => [
                self::VALUE => self::WRITE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::INSERT_OXYGEN => [
            self::SUCCESS => [
                self::VALUE => self::INSERT_OXYGEN,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::RETRIEVE_OXYGEN => [
            self::SUCCESS => [
                self::VALUE => self::RETRIEVE_OXYGEN,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::COOK => [
            self::SUCCESS => [
                self::VALUE => self::COOK_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::COFFEE => [
            self::SUCCESS => [
                self::VALUE => self::COFFEE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::DISPENSE => [
            self::SUCCESS => [
                self::VALUE => self::DISPENSE_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::SHOWER => [
            self::SUCCESS => [
                self::VALUE => self::SHOWER_HUMAN,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
            self::FAIL => [
                self::VALUE => self::SHOWER_MUSH,
                self::VISIBILITY => VisibilityEnum::PRIVATE,
            ],
        ],
        ActionEnum::LIE_DOWN => [
            self::SUCCESS => [
                self::VALUE => self::LIE_DOWN,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::GET_UP => [
            self::SUCCESS => [
                self::VALUE => self::GET_UP,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
        ActionEnum::HIT => [
            self::SUCCESS => [
                self::VALUE => self::HIT_SUCCESS,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
            self::FAIL => [
                self::VALUE => self::HIT_FAIL,
                self::VISIBILITY => VisibilityEnum::PUBLIC,
            ],
        ],
    ];
}
