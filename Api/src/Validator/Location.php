<?php


namespace Mush\Action\Validator;

use Mush\Equipment\Enum\ReachEnum;

class Location extends ClassConstraint
{
    public string $message = 'equipment not in correct location';
    public string $location = ReachEnum::INVENTORY;
}