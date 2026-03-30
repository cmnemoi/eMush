<?php

namespace Mush\Equipment\Enum;

use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Entity\Mechanics\Entity;
use Mush\Equipment\Entity\Mechanics\Exploration;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Kit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Plumbing;

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
    public const string KIT = 'kit';
    public const string PLANT = 'plant';
    public const string PLUMBING = 'plumbing';

    /** Equipment that's only visible to owner. */
    public const string RATION = 'ration';
    public const string TOOL = 'tool';
    public const string WEAPON = 'weapon';
    public const string CONTAINER = 'container';

    public static function getClassByName(string $name): string
    {
        return match ($name) {
            self::BLUEPRINT => Blueprint::class,
            self::BOOK => Book::class,
            self::DOCUMENT => Document::class,
            self::DRUG => Drug::class,
            self::ENTITY => Entity::class,
            self::EXPLORATION => Exploration::class,
            self::FRUIT => Fruit::class,
            self::GEAR => Gear::class,
            self::KIT => Kit::class,
            self::PLANT => Plant::class,
            self::PLUMBING => Plumbing::class,
            default => throw new \InvalidArgumentException('no mechanic with that name exist.')
        };
    }
}
