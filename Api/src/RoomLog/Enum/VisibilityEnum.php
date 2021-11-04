<?php

namespace Mush\RoomLog\Enum;

class VisibilityEnum
{
    public const PUBLIC = 'public';
    public const PRIVATE = 'private';
    public const COVERT = 'covert'; // revealed by camera
    public const SECRET = 'secret'; // revealed by camera or someone
    public const REVEALED = 'revealed'; // secret or covert action that has been revealed
    public const MUSH = 'mush'; // logs in mush channel
    public const HUMAN = 'human'; // not visible by mush
    public const HIDDEN = 'hidden'; // internal status

    public const COOK_RESTRICTED = 'cook_restricted'; //perishable status only visible to cooks

    //@TODO move this enum in a more general folder (game or Daedalus) as it is used for status
}
