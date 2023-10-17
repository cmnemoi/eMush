<?php

namespace Mush\Action\Validator;

/**
 * Raise a violation if the player already discovered the maximum number of planets allowed by their character config.
 */
class NumberOfDiscoverablePlanets extends ClassConstraint
{
    public string $message = 'you already discovered the maximum number of planets';
}
