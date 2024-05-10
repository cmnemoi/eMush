<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player does not have the required skill.
 */
final class HasSkill extends ClassConstraint
{
    public string $message = 'You do not have the required skill to perform this action.';

    public string $skill;
}
