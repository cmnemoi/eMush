<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Prevents action if the player does not have the skill satifying the Neron Crew Lock (for example Pilot for Piloting crew lock).
 */
final class NeronCrewLock extends ClassConstraint
{
    public string $message = 'player do not have the skill satisfying the Neron Crew Lock';

    public array $terminals = [];
}
