<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class AllXylophDatabasesDecoded extends ClassConstraint
{
    public string $message = 'There is no more XylophEntry databases to unlock!';
}
