<?php

namespace Mush\Action\Validator;

class UsedTool extends ClassConstraint
{
    public string $message = 'tool cannot be found for the action';
}
