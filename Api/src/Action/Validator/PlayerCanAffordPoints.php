<?php

namespace Mush\Action\Validator;

class PlayerCanAffordPoints extends ClassConstraint
{
    public string $message = 'not enough action point';
}
