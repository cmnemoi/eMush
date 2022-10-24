<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the conditions applied on modifiers
 * Modifier is only applied if the condition is valid.
 *
 * MAX_POINT: modify the max amount of a player or Daedalus variable
 * ACTIONS: apply on all actions
 * INJURY: when the player is hurt
 *
 * EVENT_CLUMSINESS: change the injury rate of actions
 * EVENT_DIRTY: change the chances of getting dirty on an action
 * EVENT_ACTION_MOVEMENT_CONVERSION
 *
 * CYCLE_CHANGE:
 */
class ModifierScopeEnum
{
    public const MAX_POINT = 'max_point';

    public const ACTIONS = 'actions';
    public const INJURY = 'injury';

    public const EVENT_CLUMSINESS = 'event_clumsiness';
    public const EVENT_DIRTY = 'event_dirty';
    public const EVENT_ACTION_MOVEMENT_CONVERSION = 'event_action_movement_conversion';

    public const CYCLE_CHANGE = 'cycle_change';
}
