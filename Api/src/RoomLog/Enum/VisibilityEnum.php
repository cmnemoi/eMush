<?php

namespace Mush\RoomLog\Enum;

class VisibilityEnum
{
    public const PUBLIC = 'public';
    public const PRIVATE = 'private';
    public const COVERT = 'covert'; // revealed by camera
    public const SECRET = 'secret'; // revealed by camera or someone
    public const MUSH = 'mush'; // logs in mush channel
    public const HUMAN = 'human'; // not visible by mush
    public const HIDDEN = 'hidden'; // internal status

    //some status are applied on both player and equipment with different visibility on each hidden is default
    public const EQUIPMENT_PRIVATE = 'equipment_private';
    public const PLAYER_PUBLIC = 'player_public';

    public const COOK_RESTRICTED = 'cook_restricted'; //perishable status only visible to cooks


    //@TODO move this enum in a more general folder (game or Daedalus) as it is used for status
}
