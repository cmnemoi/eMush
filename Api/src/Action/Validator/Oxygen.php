<?php

namespace Mush\Action\Validator;

class Oxygen extends ClassConstraint
{
    public string $message = 'cannot add or remove oxygen';
    public bool $retrieve = true; // If it's not retrieve the then it's insert
}
