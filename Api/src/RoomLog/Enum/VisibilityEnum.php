<?php

namespace Mush\RoomLog\Enum;

class VisibilityEnum
{
    public const PUBLIC = 'public';
    public const PRIVATE = 'private';
    public const COVERT = 'covert'; // revealed by camera
    public const SECRET = 'secret'; // revealed by camera or someone
    public const MUSH = 'mush'; // logs in mush channel
    public const HIDDEN = 'hidden'; // internal status
}
