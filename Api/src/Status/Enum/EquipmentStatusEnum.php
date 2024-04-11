<?php

namespace Mush\Status\Enum;

class EquipmentStatusEnum
{
    public const ALIEN_ARTEFACT = 'alien_artefact';
    public const ELECTRIC_CHARGES = 'electric_charges';
    public const HEAVY = 'heavy';
    public const MODULE_ACCESS = 'module_access';
    public const HIDDEN = 'hidden';
    public const BROKEN = 'broken';
    public const UNSTABLE = 'unstable';
    public const HAZARDOUS = 'hazardous';
    public const DECOMPOSING = 'decomposing';
    public const FROZEN = 'frozen';
    public const CONTAMINATED = 'contaminated';
    public const PLANT_YOUNG = 'plant_young';
    public const PLANT_THIRSTY = 'plant_thirsty';
    public const PLANT_DRY = 'plant_dry';
    public const PLANT_DISEASED = 'plant_diseased';
    public const DOCUMENT_CONTENT = 'document_content';
    public const REINFORCED = 'reinforced';
    public const PATROL_SHIP_ARMOR = 'patrol_ship_armor';
    public const SINK_CHARGE = 'sink_charge';

    public const UPDATING = 'updating';

    public static function splitItemPileStatus(): array
    {
        return [
            self::HIDDEN,
            self::BROKEN,
            self::DECOMPOSING,
            self::UNSTABLE,
            self::HAZARDOUS,
            self::FROZEN,
        ];
    }

    public static function getOutOfOrderStatuses(): array
    {
        return [
            self::BROKEN,
            self::UPDATING,
        ];
    }
}
