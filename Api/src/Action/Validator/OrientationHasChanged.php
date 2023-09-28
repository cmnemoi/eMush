<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class OrientationHasChanged extends ClassConstraint
{
    public string $message = 'Orientation has not changed';
}
