<?php

namespace Mush\Game\Enum;

/**
 * Class enumerating the status of the game
 * Scope describe the relation between the active player and the entity that provide the action.
 *
 * STANDBY: the Daedalus has been created no player joined yet
 * STARTING: a player joined the ship, but mushs have not been designated (equivalent of a lobby). Additional players can join.
 * CURRENT: the game started, mushs have been designated, player cannot join anymore.
 * FINISHED: the game is finished all players are dead or on Sol. But player still need to validate their death.
 * CLOSED: all player have validated their death.
 */
abstract class GameStatusEnum
{
    public const string STANDBY = 'standby';
    public const string STARTING = 'starting';
    public const string CURRENT = 'in_game';
    public const string FINISHED = 'finished';
    public const string CLOSED = 'closed';
}
