<?php

namespace Mush\Action\Validator;

class MushSpore extends ClassConstraint
{
    public string $message = 'spore threshold passed';

    //If 0 check greater than 0 else check it's less than threshold
    public int $threshold = 0;
}
