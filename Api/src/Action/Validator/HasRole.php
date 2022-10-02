<?php

namespace Mush\Action\Validator;

class HasRole extends ClassConstraint
{
    public string $message = 'user does not have the role to do this action';

    public string $role;
}
