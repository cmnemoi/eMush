<?php

namespace Mush\Action\Validator;

class IsSuperAdmin extends ClassConstraint
{
    public string $message = 'user is not an admin';
}
