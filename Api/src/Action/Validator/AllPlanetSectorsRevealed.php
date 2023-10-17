<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if the planet passed as parameter has all its sectors revealed.
 */
final class AllPlanetSectorsRevealed extends ClassConstraint
{
    public string $message = 'all planet sectors are revealed';
}
