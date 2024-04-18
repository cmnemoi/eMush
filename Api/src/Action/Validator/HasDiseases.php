<?php

namespace Mush\Action\Validator;

class HasDiseases extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'character has already all fake diseases';

    public ?string $type = null;

    public bool $isEmpty = true;

    // If not target player, then it targets the parameter
    public string $target = self::PARAMETER;
}
