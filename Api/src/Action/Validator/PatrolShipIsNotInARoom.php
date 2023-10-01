<?php

namespace Mush\Action\Validator;

class PatrolShipIsNotInARoom extends ClassConstraint
{
    public string $message = 'this action is not possible if patrol ship is not in a room';
}
