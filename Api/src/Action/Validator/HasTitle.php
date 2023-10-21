<?php

namespace Mush\Action\Validator;

class HasTitle extends ClassConstraint
{
    public string $message = 'player does not have the needed title to do this action';

    public string $title;

    public string $terminal;
}
