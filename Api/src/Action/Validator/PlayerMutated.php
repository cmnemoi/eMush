<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

final class PlayerMutated extends ClassConstraint
{
    public string $message = 'A mutated can\'t do that. He can only move, sabotage and strike, and that\'s more than enough...';
}
