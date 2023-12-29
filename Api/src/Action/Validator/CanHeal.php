<?php

namespace Mush\Action\Validator;

/**
 * This class implements a constraint to check if the player can heal the target
 * If the player is in medlab :
 *  - heal possible either if the player health is not max OR if he has a disease
 *  If the player uses the medikit :
 *   - heal possible only if the player health is not max.
 */
class CanHeal extends ClassConstraint
{
    public const PLAYER = 'player';
    public const PARAMETER = 'parameter';

    public string $message = 'condition are not met to heal this player';

    // If not target player, then it targets the parameter
    public string $target = self::PARAMETER;
}
