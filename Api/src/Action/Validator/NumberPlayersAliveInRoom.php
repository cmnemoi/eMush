<?php

namespace Mush\Action\Validator;

class NumberPlayersAliveInRoom extends ClassConstraint
{
    public int $number;

    public string $message = 'there are too many or too few people in room';
}
