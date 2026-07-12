<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class EmptyPlaceToSit extends ClassConstraint
{
    public string $message = 'There is no empty bed in room or chairs.';
}
