<?php

namespace Mush\Game\Enum;

/**
 * Class enumerating the visibility of different game element
 * This mainly apply to RoomLogs and Statuses.
 *
 * PUBLIC: everyone in the daedalus can see this element
 * PRIVATE: only the current player can see this element
 * COVERT: only current player can see this element unless a camera is in the room, in this case, the element become REVEALED
 * SECRET: only current player can see this element unless a camera or someone else is in the room, in this case, the element become REVEALED
 * REVEALED: secret or covert action that has been revealed
 * MUSH: only player with mush status can see this element
 * HUMAN: only human player can see this element
 * HIDDEN: this element is not visible to anyone but administrators and developers
 */
abstract class VisibilityEnum
{
    public const string PUBLIC = 'public';
    public const string PRIVATE = 'private';
    public const string COVERT = 'covert'; // revealed by camera
    public const string SECRET = 'secret'; // revealed by camera or someone
    public const string REVEALED = 'revealed'; // secret or covert action that has been revealed
    public const string MUSH = 'mush'; // logs in mush channel
    public const string HUMAN = 'human'; // not visible by mush
    public const string HIDDEN = 'hidden'; // internal status
}
