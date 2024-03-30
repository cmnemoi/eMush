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
    public const BAN_USER = 'ban_user';
    public const QUARANTINE_PLAYER = 'quarantine_player';
    public const WARNING = 'warning';
    public const DELETE_MESSAGE = 'delete_message';
    public const DELETE_END_MESSAGE = 'delete_end_message';
    public const HIDE_END_MESSAGE = 'hide_message';

    // reasons for the ban
    public const MULTI_ACCOUNT = 'multi_account';
    public const MUSH_PLAY_AGAINST_TEAM = 'mush_play_against_team';
    public const KILLING_SPREE = 'killing_spree';
    public const FOUL_PLAY = 'foul_play';
    public const SUICIDE = 'suicide';
    public const HATE_SPEECH = 'hate_speech';
    public const FLOOD = 'flood';
    public const WRONG_LANGUAGE = 'wrong_language';
    public const EXPLOIT = 'exploit';
    public const EXPLOIT_INCENTIVE = 'exploit_incentive';
    public const LEAKING_PRIVATE_INFORMATION = 'leaking_private_information';
}
