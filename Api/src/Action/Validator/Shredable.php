<?php

namespace Mush\Action\Validator;

class Shredable extends ClassConstraint
{
    public string $message = 'cannot shred the parameter';
}
