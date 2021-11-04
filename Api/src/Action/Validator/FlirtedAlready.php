<?php

namespace Mush\Action\Validator;

class FlirtedAlready extends ClassConstraint
{
    public string $message = 'flirts did not match with expected values';

    //if false expects no flirts
    public bool $expectedValue = false;

    //the initiator is current player if true, target player if false
    public bool $initiator = true;
}
