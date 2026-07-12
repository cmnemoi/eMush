<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

class BondedAlready extends ClassConstraint
{
    public string $message = 'bonds did not match with expected values';

    // if false expects no bonds
    public bool $expectedValue = false;

    // the initiator is current player if true, target player if false
    public bool $initiator = true;
}
