<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum XylophEnum: string
{
    case BLUEPRINTS = 'blueprints';
    case COOK = 'cook';
    case DISK = 'disk';
    case GHOST_CHUN = 'ghost_chun';
    case GHOST_SAMPLE = 'ghost_sample';
    case KIVANC = 'kivanc';
    case LIST = 'list';
    case MAGE_BOOKS = 'mage_books';
    case MAGNETITE = 'magnetite';
    case NOTHING = 'nothing';
    case NULL = '';
    case SNOW = 'snow';
    case UNKNOWN = 'unknown';
    case VERSION = 'version';

    public function toString(): string
    {
        return $this->value;
    }

    public static function requiresPrinting(self $entry): bool
    {
        return \in_array($entry, [
            self::BLUEPRINTS,
            self::COOK,
            self::LIST,
            self::MAGE_BOOKS,
        ], true);
    }
}
