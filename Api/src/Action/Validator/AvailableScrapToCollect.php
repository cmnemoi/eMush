<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raise a constraint violation if there is no scrap to collect in space.
 */
final class AvailableScrapToCollect extends ClassConstraint
{
    public string $message = 'there is no scrap to collect';
}
