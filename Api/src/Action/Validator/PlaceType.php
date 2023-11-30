<?php

namespace Mush\Action\Validator;

class PlaceType extends ClassConstraint
{
    public string $message = 'place is not the expected type';
    public string $type;

    public bool $isType = true;
}
