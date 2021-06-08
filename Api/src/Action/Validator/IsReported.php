<?php

namespace Mush\Action\Validator;

class IsReported extends ClassConstraint
{
    public string $message = 'this has already been reported';
}
