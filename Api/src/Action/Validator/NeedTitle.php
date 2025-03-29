<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

/**
 * Raises a violation if player does not have the required title.
 *
 * @param string $title                        The title required to do the action
 * @param bool   $allowsIfTitleHasNotBeenGiven If true, the action is allowed if there is no player currently holding the title (dead, or before Mush selection)
 */
final class NeedTitle extends ClassConstraint
{
    public string $message = 'player does not have the needed title to do this action';

    public string $title;
    public bool $allowIfNoPlayerHasTheTitle = false;
}
