<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class EquipmentReachable extends ClassConstraint
{
    public string $message = 'cannot reach equipment';
    public string $name;
}
