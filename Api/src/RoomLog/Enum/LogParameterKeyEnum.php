<?php

namespace Mush\RoomLog\Enum;

class LogParameterKeyEnum
{
    public const DISEASE = 'disease';
    public const PLAYER = 'target_character'; //Carefull, this is always the targeted player
    public const EQUIPMENT = 'targetEquipment';
    public const ITEM = 'targetItem';
}
