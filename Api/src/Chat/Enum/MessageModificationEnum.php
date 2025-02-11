<?php

declare(strict_types=1);

namespace Mush\Chat\Enum;

abstract class MessageModificationEnum
{
    public const string PARANOIA_MESSAGES = 'paranoia_messages';
    public const string PARANOIA_DENIAL = 'paranoia_denial';
    public const string DEAF_LISTEN = 'deaf_listen';
    public const string COPROLALIA_MESSAGES = 'coprolalia_messages';
    public const string DEAF_SPEAK = 'deaf_speak';
    public const string PATULINE_SCRAMBLER_MODIFICATION = 'patuline_scrambler_modification';
}
