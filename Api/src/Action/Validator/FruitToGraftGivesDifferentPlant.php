<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if grafting a fruit would give the same plant.
 */
final class FruitToGraftGivesDifferentPlant extends ClassConstraint
{
    public string $message = 'You cannot graft a fruit that would give the same plant.';
}
