<?php

namespace Mush\Action\Validator;

class PlaceType extends ClassConstraint
{
    public string $message = 'place is not the expected type';
    public string $type;

    // true : the constraint return a violation if the type does not match - false : violation if the type match
    public bool $allowIfTypeMatches = true;
}
