<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player does not have the required title.
 */
final class NeedTitle extends ClassConstraint
{
    public string $message = 'player does not have the needed title to do this action';

    public string $title;
}
