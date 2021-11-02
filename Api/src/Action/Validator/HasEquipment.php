<?php

namespace Mush\Action\Validator;

class HasEquipment extends ClassConstraint
{
    public string $message = 'an equipment is missing or should not be present';

    public string $reach;
    public string $equipment;

    public bool $contains = true;

    //if true, checks that the equipment is currently working
    public bool $checkIfOperational = false;
}
