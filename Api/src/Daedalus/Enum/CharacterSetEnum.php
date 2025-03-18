<?php

namespace Mush\Daedalus\Enum;

/**
 * Class enumerating options for character toggles
 * ALL: All characters for this option are enabled (e.g. Chao, Finola, Derek, Andie)
 * NONE: All characters for this option are disabled
 * ONE: One random set of characters for these seats is picked (e.g. Chao & Finola OR Derek & Andie)
 * RANDOM: Random characters are enabled (e.g. Chao & Derek OR Chao & Andie, etc.)
 * CHARACTER NAMES: specify a set to allow in.
 * !!! Toggles are ignored for the April Fools event.
 */
class CharacterSetEnum
{
    public const ALL = 'all';
    public const NONE = 'none';
    public const ONE = 'one';
    public const RANDOM = 'random';
    public const FINOLA_CHAO = 'finola_chao';
    public const ANDIE_DEREK = 'andie_derek';
}
