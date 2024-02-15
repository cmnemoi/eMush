<?php

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class ItemEnum
{
    public const BABEL_MODULE = 'babel_module';
    public const DRILL = 'drill';
    public const ECHOLOCATOR = 'echolocator';
    public const QUADRIMETRIC_COMPASS = 'quadrimetric_compass';
    public const ROPE = 'rope';
    public const THERMOSENSOR = 'thermosensor';
    public const WHITE_FLAG = 'white_flag';
    public const OLD_T_SHIRT = 'old_t_shirt';
    public const PLASTIC_SCRAPS = 'plastic_scraps';
    public const METAL_SCRAPS = 'metal_scraps';
    public const THICK_TUBE = 'thick_tube';
    public const CAMERA_ITEM = 'camera_item';
    public const MUSH_GENOME_DISK = 'mush_genome_disk';
    public const MUSH_SAMPLE = 'mush_sample';
    public const MYCO_ALARM = 'myco_alarm';
    public const STARMAP_FRAGMENT = 'starmap_fragment';
    public const WATER_STICK = 'water_stick';
    public const OLD_FAITHFUL = 'old_faithful';
    public const LIZARO_JUNGLE = 'lizaro_jungle';
    public const KNIFE = 'knife';
    public const BLASTER = 'blaster';
    public const NATAMY_RIFLE = 'natamy_rifle';
    public const GRENADE = 'grenade';
    public const ROCKET_LAUNCHER = 'rocket_launcher';
    public const TRACKER = 'tracker';
    public const WALKIE_TALKIE = 'walkie_talkie';
    public const ITRACKIE = 'itrackie';
    public const OXYGEN_CAPSULE = 'oxygen_capsule';
    public const FUEL_CAPSULE = 'fuel_capsule';
    public const APPRENTON = 'apprenton';
    public const BLUEPRINT = 'blueprint';
    public const DOCUMENT = 'document';
    public const COMMANDERS_MANUAL = 'commanders_manual';
    public const MUSH_RESEARCH_REVIEW = 'mush_research_review';
    public const POST_IT = 'post_it';
    public const HYDROPOT = 'hydropot';
    public const SCHRODINGER = 'schrodinger';

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
        ]);
    }
}
