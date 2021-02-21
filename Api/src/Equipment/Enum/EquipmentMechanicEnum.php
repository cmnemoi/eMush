<?php

namespace Mush\Equipment\Enum;

class EquipmentMechanicEnum
{
    public const RATION = 'ration';
    public const GEAR = 'gear';
    public const TOOL = 'tools';
    public const WEAPON = 'weapon';
    public const EXPLORATION = 'exploration';
    public const INSTRUMENT = 'instrument';
    public const FRUIT = 'fruit';
    public const PLANT = 'plant';
    public const DRUG = 'drug';
    public const BOOK = 'book';
    public const BLUEPRINT = 'blueprint';
    public const COMPONENT = 'component';
    public const DOCUMENT = 'document';
    public const ENTITY = 'entity';
    public const DISMOUNTABLE = 'dismountable';
    public const CHARGED = 'charged';

    public static function getAll(): array
    {
        return [
            self::RATION,
            self::GEAR,
            self::TOOL,
            self::WEAPON,
            self::EXPLORATION,
            self::INSTRUMENT,
            self::FRUIT,
            self::PLANT,
            self::DRUG,
            self::BOOK,
            self::BLUEPRINT,
            self::COMPONENT,
            self::DOCUMENT,
            self::ENTITY,
            self::DISMOUNTABLE,
            self::CHARGED
        ];
    }
}
