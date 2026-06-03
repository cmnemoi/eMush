<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class EmptyOperationalBedInRoom extends ClassConstraint
{
    public string $message = 'There is no empty bed in room';
}
