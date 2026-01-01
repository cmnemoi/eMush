<?php

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class ItemEnum
{
    public const string BABEL_MODULE = 'babel_module';
    public const string DRILL = 'drill';
    public const string ECHOLOCATOR = 'echolocator';
    public const string QUADRIMETRIC_COMPASS = 'quadrimetric_compass';
    public const string ROPE = 'rope';
    public const string THERMOSENSOR = 'thermosensor';
    public const string WHITE_FLAG = 'white_flag';
    public const string OLD_T_SHIRT = 'old_t_shirt';
    public const string PLASTIC_SCRAPS = 'plastic_scraps';
    public const string METAL_SCRAPS = 'metal_scraps';
    public const string THICK_TUBE = 'thick_tube';
    public const string CAMERA_ITEM = 'camera_item';
    public const string MUSH_GENOME_DISK = 'mush_genome_disk';
    public const string MUSH_SAMPLE = 'mush_sample';
    public const string MYCO_ALARM = 'myco_alarm';
    public const string STARMAP_FRAGMENT = 'starmap_fragment';
    public const string WATER_STICK = 'water_stick';
    public const string OLD_FAITHFUL = 'old_faithful';
    public const string LIZARO_JUNGLE = 'lizaro_jungle';
    public const string KNIFE = 'knife';
    public const string BLASTER = 'blaster';
    public const string NATAMY_RIFLE = 'natamy_rifle';
    public const string GRENADE = 'grenade';
    public const string ROCKET_LAUNCHER = 'rocket_launcher';
    public const string BARE_HANDS = 'bare_hands';
    public const string TRACKER = 'tracker';
    public const string WALKIE_TALKIE = 'walkie_talkie';
    public const string ITRACKIE = 'itrackie';
    public const string ITRACKIE_2 = 'itrackie_2';
    public const string OXYGEN_CAPSULE = 'oxygen_capsule';
    public const string FUEL_CAPSULE = 'fuel_capsule';
    public const string APPRENTRON = 'apprentron';
    public const string BLUEPRINT = 'blueprint';
    public const string DOCUMENT = 'document';
    public const string KIT = 'kit';
    public const string COMMANDERS_MANUAL = 'commanders_manual';
    public const string MUSH_RESEARCH_REVIEW = 'mush_research_review';
    public const string POST_IT = 'post_it';
    public const string HYDROPOT = 'hydropot';
    public const string SCHRODINGER = 'schrodinger';
    public const string SUPPORT_DRONE = 'support_drone';
    public const string PAVLOV = 'pavlov';
    public const string ANNIVERSARY_GIFT = 'anniversary_gift';
    public const string COFFEE_THERMOS = 'coffee_thermos';
    public const string STANDARD_RATION = 'standard_ration';
    public const string LUNCHBOX = 'lunchbox';
    public const string LUMP_OF_COAL = 'lump_of_coal';
    public const string EVIL_COMPASS = 'evil_compass';
    public const string FRUIT_BASKET = 'fruit_basket';
    public const string STRAWMAN = 'strawman';
    public const string CHEFS_KNIFE = 'chefs_knife';
    public const string LUCKY_LIZARO = 'lucky_lizaro';
    public const string SPACESHIP_KEYS = 'spaceship_keys';
    public const string MULTIPASS = 'multipass';
    public const string DEBRIS_PRINTER = 'debris_printer';
    public const string ANTIQUE_PERFUME_ITEM = 'antique_perfume_item';
    public const string MEGAPHONE = 'megaphone';
    public const string SHIP_BLANKET = 'ship_blanket';
    public const string SCHOOLBOOKS = 'schoolbooks';

    public static function getArtefacts(): ArrayCollection
    {
        return new ArrayCollection([
            GearItemEnum::ALIEN_BOTTLE_OPENER,
            GearItemEnum::ROLLING_BOULDER,
            ToolItemEnum::ALIEN_HOLOGRAPHIC_TV,
            GearItemEnum::INVERTEBRATE_SHELL,
            GearItemEnum::MAGELLAN_LIQUID_MAP,
            GearItemEnum::PRINTED_CIRCUIT_JELLY,
            self::STARMAP_FRAGMENT,
            self::WATER_STICK,
            ToolItemEnum::JAR_OF_ALIEN_OIL,
        ]);
    }

    public static function getWeapons(): ArrayCollection
    {
        return new ArrayCollection([
            self::KNIFE,
            self::BLASTER,
            self::NATAMY_RIFLE,
            self::GRENADE,
            self::ROCKET_LAUNCHER,
            self::LIZARO_JUNGLE,
            self::OLD_FAITHFUL,
            self::CHEFS_KNIFE,
            self::LUCKY_LIZARO,
        ]);
    }

    public static function getBlades(): ArrayCollection
    {
        return new ArrayCollection([
            self::KNIFE,
            self::CHEFS_KNIFE,
        ]);
    }

    public static function getGuns(): ArrayCollection
    {
        return new ArrayCollection([
            self::BLASTER,
            self::NATAMY_RIFLE,
            self::ROCKET_LAUNCHER,
            self::LIZARO_JUNGLE,
            self::OLD_FAITHFUL,
            self::LUCKY_LIZARO,
        ]);
    }
}
