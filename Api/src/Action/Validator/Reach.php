<?php

namespace Mush\Action\Validator;

class Reach extends ClassConstraint
{
    public string $message = 'player cannot reach parameter';
    public string $reach;
}
