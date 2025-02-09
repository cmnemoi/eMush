<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class LinkWithSolConstraint extends ClassConstraint
{
    public string $message = 'Link with Sol does not meet expected state';
    public bool $shouldBeEstablished;
}
