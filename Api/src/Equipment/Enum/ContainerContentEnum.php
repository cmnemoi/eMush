<?php

namespace Mush\Equipment\Enum;

use Mush\Game\Enum\CharacterEnum;

class ContainerContentEnum
{
    public const FILTER_BY_CHARACTER = 'character';
    public const array SPACE_CAPSULE_CONTENT = [
        ItemEnum::FUEL_CAPSULE => 1,
        ItemEnum::OXYGEN_CAPSULE => 1,
        ItemEnum::METAL_SCRAPS => 1,
        ItemEnum::PLASTIC_SCRAPS => 1,
    ];

    public const array COFFEE_THERMOS_CONTENT = [
        [
            'item' => GameRationEnum::COFFEE,
            'quantity' => 1,
            'weight' => 1,
        ],
    ];

    public const array LUNCHBOX_CONTENT = [
        [
            'item' => GameRationEnum::STANDARD_RATION,
            'quantity' => 1,
            'weight' => 1,
        ],
    ];

    public const array FRUIT_BASKET_CONTENT = [
        [
            'item' => GameFruitEnum::CREEPNUT,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::MEZTINE,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::GUNTIFLOP,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::PLOSHMINA,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::PRECATI,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::BOTTINE,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::FRAGILANE,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::ANEMOLE,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::PENICRAFT,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::KUBINUS,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::CALEBOOT,
            'quantity' => 1,
            'weight' => 1,
        ],
        [
            'item' => GameFruitEnum::FILANDRA,
            'quantity' => 1,
            'weight' => 1,
        ],
    ];

    public const array ANNIVERSARY_GIFT_CONTENT = [
        [
            'item' => GearItemEnum::PLASTENITE_ARMOR, // @TODO: Lucky Lizaro
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHAO,
        ],
        [
            'item' => GameRationEnum::ORGANIC_WASTE,
            'quantity' => 2,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHAO,
        ],
        [
            'item' => 'apprentron_gunner',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHAO,
        ],
        [
            'item' => ToolItemEnum::MAD_KUBE, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHAO,
        ],
        [
            'item' => 'apprentron_medic',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHUN,
        ],
        [
            'item' => 'apprentron_optimist',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHUN,
        ],
        [
            'item' => ItemEnum::MYCO_ALARM,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHUN,
        ],
        [
            'item' => ItemEnum::MUSH_SAMPLE, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::CHUN,
        ],
        [
            'item' => 'apprentron_sprinter',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ELEESHA,
        ],
        [
            'item' => ItemEnum::ITRACKIE_2,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ELEESHA,
        ],
        [
            'item' => ToolItemEnum::BLOCK_OF_POST_IT,
            'quantity' => 2,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ELEESHA,
        ],
        [
            'item' => ToolItemEnum::DUCT_TAPE, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ELEESHA,
        ],
        [
            'item' => ToolItemEnum::MEDIKIT, // @TODO: find replacement
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FINOLA,
        ],
        [
            'item' => ToolItemEnum::BANDAGE,
            'quantity' => 4,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FINOLA,
        ],
        [
            'item' => GearItemEnum::SOAP,
            'quantity' => 2,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FINOLA,
        ],
        [
            'item' => ItemEnum::WATER_STICK, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FINOLA,
        ],
        [
            'item' => GearItemEnum::MAGELLAN_LIQUID_MAP, // @TODO: "Antique Perfume...?" item
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FRIEDA,
        ],
        [
            'item' => GearItemEnum::MAGELLAN_LIQUID_MAP,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FRIEDA,
        ],
        [
            'item' => 'apprentron_lethargy',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FRIEDA,
        ],
        [
            'item' => ToolItemEnum::SUPERFREEZER, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::FRIEDA,
        ],
        [
            'item' => 'apprentron_apprentice',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::GIOELE,
        ],
        [
            'item' => 'apprentron_sneak',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::GIOELE,
        ],
        [
            'item' => ItemEnum::COFFEE_THERMOS,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::GIOELE,
        ],
        [
            'item' => ItemEnum::LUMP_OF_COAL,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::GIOELE,
        ],
        [
            'item' => 'apprentron_shooter',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::HUA,
        ],
        [
            'item' => GearItemEnum::SNIPER_HELMET,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::HUA,
        ],
        [
            'item' => ItemEnum::QUADRIMETRIC_COMPASS,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::HUA,
        ],
        [
            'item' => ItemEnum::EVIL_COMPASS, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::HUA,
        ],
        [
            'item' => GameFruitEnum::JUMPKIN,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::IAN,
        ],
        [
            'item' => ItemEnum::HYDROPOT,
            'quantity' => 2,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::IAN,
        ],
        [
            'item' => ToolItemEnum::EXTINGUISHER, // @TODO: Alien Fruit Basket
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::IAN,
        ],
        [
            'item' => ItemEnum::WHITE_FLAG, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::IAN,
        ],
        [
            'item' => EquipmentEnum::SWEDISH_SOFA . '_kit',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JANICE,
        ],
        [
            'item' => ItemEnum::THERMOSENSOR,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JANICE,
        ],
        [
            'item' => ItemEnum::BABEL_MODULE,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JANICE,
        ],
        [
            'item' => ToolItemEnum::ALIEN_HOLOGRAPHIC_TV, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JANICE,
        ],
        [
            'item' => ItemEnum::STARMAP_FRAGMENT,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JIN_SU,
        ],
        [
            'item' => GameRationEnum::SPACE_POTATO,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JIN_SU,
        ],
        [
            'item' => 'apprentron_diplomat',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JIN_SU,
        ],
        [
            'item' => 'apprentron_politician', // gag... give to stephen instead? need a replacement if so
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::JIN_SU,
        ],
        [
            'item' => 'apprentron_it_expert', // make this standard gift? find replacement?
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::KUAN_TI,
        ],
        [
            'item' => GearItemEnum::ADJUSTABLE_WRENCH, // @TODO: find replacement
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::KUAN_TI,
        ],
        [
            'item' => GearItemEnum::ALIEN_BOTTLE_OPENER, // @TODO: find replacement
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::KUAN_TI,
        ],
        [
            'item' => GearItemEnum::SPACESUIT, // gag
            'quantity' => 10,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::KUAN_TI,
        ],
        [
            'item' => ItemEnum::STRAWMAN,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::PAOLA,
        ],
        [
            'item' => EquipmentEnum::JUKEBOX . '_kit',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::PAOLA,
        ],
        [
            'item' => GearItemEnum::INVERTEBRATE_SHELL,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::PAOLA,
        ],
        [
            'item' => EquipmentEnum::SWEDISH_SOFA . '_weird_blueprint', // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::PAOLA,
        ],
        [
            'item' => 'apprentron_ocd',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::RALUCA,
        ],
        [
            'item' => GearItemEnum::OSCILLOSCOPE,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::RALUCA,
        ],
        [
            'item' => GameRationEnum::SUPERVITAMIN_BAR,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::RALUCA,
        ],
        [
            'item' => ItemEnum::SCHRODINGER, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::RALUCA,
        ],
        [
            'item' => GearItemEnum::ROLLING_BOULDER,
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ROLAND,
        ],
        [
            'item' => GearItemEnum::NCC_LENS,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ROLAND,
        ],
        [
            'item' => ToolItemEnum::EXTINGUISHER, // @TODO: find replacement
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ROLAND,
        ],
        [
            'item' => 'apprentron_motivator', // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ROLAND,
        ],
        [
            'item' => GearItemEnum::STAINPROOF_APRON, // @TODO: Chef's Knife
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::STEPHEN,
        ],
        [
            'item' => 'apprentron_caffeine_junkie',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::STEPHEN,
        ],
        [
            'item' => GameRationEnum::LOMBRICK_BAR, // @TODO: check Lombrick Bar is setup and coded
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::STEPHEN,
        ],
        [
            'item' => ToolItemEnum::MICROWAVE, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::STEPHEN,
        ],
        [
            'item' => 'apprentron_sprinter',
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::TERRENCE,
        ],
        [
            'item' => GearItemEnum::ADJUSTABLE_WRENCH,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::TERRENCE,
        ],
        [
            'item' => ItemEnum::SUPPORT_DRONE,
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::TERRENCE,
        ],
        [
            'item' => GearItemEnum::ANTIGRAV_SCOOTER, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::TERRENCE,
        ],
        [
            'item' => 'apprentron_genius', // @TODO: Forgotten school notes
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ANDIE,
        ],
        [
            'item' => 'apprentron_hygienist',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ANDIE,
        ],
        [
            'item' => 'apprentron_neron_only_friend',
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ANDIE,
        ],
        [
            'item' => 'apprentron_rebel', // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::ANDIE,
        ],
        [
            'item' => 'apprentron_pilot', // gag
            'quantity' => 1,
            'weight' => 1,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::DEREK,
        ],
        [
            'item' => ItemEnum::THICK_TUBE, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::DEREK,
        ],
        [
            'item' => ToolItemEnum::JAR_OF_ALIEN_OIL, // gag
            'quantity' => 1,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::DEREK,
        ],
        [
            'item' => GameRationEnum::ANABOLIC, // gag
            'quantity' => 3,
            'weight' => 5,
            'filterType' => self::FILTER_BY_CHARACTER,
            'filterValue' => CharacterEnum::DEREK,
        ],
    ];
}
