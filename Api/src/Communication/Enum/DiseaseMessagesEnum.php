<?php

namespace Mush\Communication\Enum;

class DiseaseMessagesEnum
{
    public const DEAF = 'deaf';
    public const REPLACE_COPROLALIA = 'replace_coprolalia';
    public const PRE_COPROLALIA = 'pre_coprolalia';
    public const POST_COPROLALIA = 'post_coprolalia';
    public const ANIMAL_COPROLALIA = 'animal_coprolalia';
    public const WORD_COPROLALIA = 'word_coprolalia';
    public const PREFIX_COPROLALIA = 'prefix_coprolalia';
    public const ADJECTIVE_COPROLALIA = 'adjective_coprolalia';
    public const BALLS_COPROLALIA = 'balls_coprolalia';

    public const ACCUSE_PARANOIA = 'accuse_paranoia';
    public const REPLACE_PARANOIA = 'replace_paranoia';
    public const PRE_PARANOIA = 'pre_paranoia';

    public const ORIGINAL_MESSAGE = 'original_message';
    public const MODIFICATION_CAUSE = 'modification_cause';

    public static function getCoprolaliaMessages(): array
    {
        return [
            self::REPLACE_COPROLALIA,
            self::PRE_COPROLALIA,
            self::POST_COPROLALIA,
        ];
    }
}
