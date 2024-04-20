<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class NoEfficiency extends ClassConstraint
{
    public string $message = 'Your efficiency is 0% for this project, participating would be useless.';
}
