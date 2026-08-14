<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class HasEquipmentOnBoard extends ClassConstraint
{
    public string $message = 'Equipment do not exist on the daedalus';
    public string $name;
}
