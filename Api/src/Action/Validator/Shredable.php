<?php

namespace Mush\Action\Validator;

final class Shredable extends ClassConstraint
{
    public string $message = 'cannot shred the parameter';
}
