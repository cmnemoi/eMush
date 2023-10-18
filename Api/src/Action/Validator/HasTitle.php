<?php

namespace Mush\Action\Validator;

class HasTitle extends ClassConstraint
{
    public string $message = 'user does not have the role to do this action';

    public string $title;

    public string $terminal;
}
