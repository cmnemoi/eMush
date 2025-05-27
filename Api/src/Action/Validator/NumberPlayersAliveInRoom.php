<?php

namespace Mush\Action\Validator;

/**
 * Raises a violation if the number of players alive in the room does not match the expected one.
 *
 * @param string  $mode      The mode to use to compare the number of players alive in the room (less_than, greater_than, equal). Default: equal
 * @param int     $number    the number of players alive in the room to compare to
 * @param ?string $placeName if not null, the place to check instead of the player's current place
 */
class NumberPlayersAliveInRoom extends ClassConstraint
{
    public const LESS_THAN = 'less_than';
    public const GREATER_THAN = 'greater_than';
    public const EQUAL = 'equal';

    public string $mode = self::EQUAL;
    public int $number;
    public ?string $placeName = null;

    public string $message = 'the number of players alive in the room does not match the expected one';
}
