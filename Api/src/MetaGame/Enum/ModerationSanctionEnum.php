<?php

namespace Mush\MetaGame\Enum;

/**
 * Class enumerating the different sanctions the moderation can take.
 *
 * BAN_USER: user cannot play or launch new ships
 * QUARANTINE_PLAYER: player is killed
 * WARNING: a warning is issued to the user
 * DELETE_MESSAGE: a message (either endMessage or a message in the chat) is deleted
 */
class ModerationSanctionEnum
{
    // moderation actions
    public const string BAN_USER = 'ban_user';
    public const string QUARANTINE_PLAYER = 'quarantine_player';
    public const string WARNING = 'warning';
    public const string DELETE_MESSAGE = 'delete_message';
    public const string DELETE_END_MESSAGE = 'delete_end_message';
    public const string HIDE_END_MESSAGE = 'hide_end_message';

    // moderation actions
    public const string REPORT = 'report';
    public const string REPORT_ABUSIVE = 'report_abusive';
    public const string REPORT_PROCESSED = 'report_processed';

    // reasons for the ban
    public const string MULTI_ACCOUNT = 'multi_account';
    public const string MUSH_PLAY_AGAINST_TEAM = 'mush_play_against_team';
    public const string KILLING_SPREE = 'killing_spree';
    public const string FOUL_PLAY = 'foul_play';
    public const string SUICIDE = 'suicide';
    public const string HATE_SPEECH = 'hate_speech';
    public const string FLOOD = 'flood';
    public const string WRONG_LANGUAGE = 'wrong_language';
    public const string EXPLOIT = 'exploit';
    public const string EXPLOIT_INCENTIVE = 'exploit_incentive';
    public const string LEAKING_PRIVATE_INFORMATION = 'leaking_private_information';

    public static function isReport(string $value): bool
    {
        return \in_array($value, [
            self::REPORT,
            self::REPORT_PROCESSED,
            self::REPORT_ABUSIVE,
        ], true);
    }
}
