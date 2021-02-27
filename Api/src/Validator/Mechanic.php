<?php

namespace Mush\Action\Validator;

use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Validator\Constraint;

class Mechanic extends ClassConstraint
{
    public string $message = 'equipment do not have the mechanic';
    public string $mechanic = EquipmentMechanicEnum::GEAR;
}