<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class AvailablePrivateChannel extends ClassConstraint
{
    public string $message = 'no available private channel slots';
}
