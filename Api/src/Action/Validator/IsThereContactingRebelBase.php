<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class IsThereContactingRebelBase extends ClassConstraint
{
    public string $message = 'There is no rebel base contacting the Daedalus.';
}
