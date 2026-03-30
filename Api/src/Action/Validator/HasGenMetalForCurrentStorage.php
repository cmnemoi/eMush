<?php

namespace Mush\Action\Validator;

final class HasGenMetalForCurrentStorage extends ClassConstraint
{
    public string $message = 'player has already generated metal in this storage';
}
