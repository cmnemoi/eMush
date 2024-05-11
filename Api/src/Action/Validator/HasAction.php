<?php

namespace Mush\Action\Validator;

class HasAction extends ClassConstraint
{
    public string $message = 'action provider do not have the action';
}
