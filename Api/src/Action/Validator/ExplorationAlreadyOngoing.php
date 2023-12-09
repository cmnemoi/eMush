<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class ExplorationAlreadyOngoing extends ClassConstraint
{
    public string $message = 'exploration already ongoing';

    public bool $allowAction = false;
}
