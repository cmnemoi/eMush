<?php


namespace Mush\Action\Validator;


use Mush\Action\Validator\ClassConstraint;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

class Oxygen extends ClassConstraint
{
    public string $message = 'cannot add or remove oxygen';
    public bool $retrieve = true; //If it's not retrieve the then it's insert
}