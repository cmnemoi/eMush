<?php

namespace Mush\Action\Validator;

/**
 * Raises a violation to access a terminal given the titles of the player.
 *
 * @param bool $allowAccess If true, will raise a violation if the player does NOT have the title. If false, will raise a violation if the player HAS the title.
 */
class HasNeededTitleForTerminal extends ClassConstraint
{
    public string $message = 'player does not have the needed title to do this action';

    public bool $allowAccess = true;
}
