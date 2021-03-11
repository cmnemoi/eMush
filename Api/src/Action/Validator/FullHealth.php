<?php


namespace Mush\Action\Validator;

class MaxHealth extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'player already full health';
}
