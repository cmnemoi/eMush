<?php

namespace Mush\Action\Validator;

class AreSymptomsPreventingAction extends ClassConstraint
{
    public string $message = 'player has symptoms which prevent this action';
}
