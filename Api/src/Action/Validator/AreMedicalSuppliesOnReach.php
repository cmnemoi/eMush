<?php

namespace Mush\Action\Validator;

/**
 * This class implements a constraint to check if the player is in the medlab.
 */
class AreMedicalSuppliesOnReach extends ClassConstraint
{
    public string $message = 'room is not medlab and the player do not carry the medikit';
}
