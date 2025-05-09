<?php

namespace Mush\Equipment\Enum;

abstract class EquipmentMechanicEnum
{
    public const string BLUEPRINT = 'blueprint';
    public const string BOOK = 'book';
    public const string DOCUMENT = 'document';
    public const string DRUG = 'drug';
    public const string ENTITY = 'entity';
    public const string EXPLORATION = 'exploration';
    public const string FRUIT = 'fruit';
    public const string GEAR = 'gear';
    public const string PATROL_SHIP = 'patrol_ship';
    public const string PLANT = 'plant';
    public const string PLUMBING = 'plumbing';
    public const string PRIVATE_EQUIPMENT = 'private_equipment';

    /** Equipment that's only visible to owner. */
    public const string RATION = 'ration';
    public const string TOOL = 'tool';
    public const string WEAPON = 'weapon';
    public const string CONTAINER = 'container';
}
