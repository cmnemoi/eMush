<?php

namespace Mush\Action\Validator;

/**
 * This class implements a constraint to check if the player is in the medlab.
 */
class IsMedlabRoom extends ClassConstraint
{
    public string $message = 'room is not medlab';

    //if true, expects room to be medlab
    public bool $expectedValue = true;
}
