<?php

namespace Mush\Action\Validator;

use Mush\Equipment\Enum\EquipmentMechanicEnum;

class Mechanic extends ClassConstraint
{
    public string $message = 'equipment do not have the mechanic';
    public string $mechanic = EquipmentMechanicEnum::GEAR;
}
