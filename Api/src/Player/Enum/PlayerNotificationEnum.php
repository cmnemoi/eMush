<?php

declare(strict_types=1);

namespace Mush\Player\Enum;

enum PlayerNotificationEnum: string
{
    case DROPPED_CRITICAL_ITEM = 'dropped_critical_item';
    case EXPLORATION_CLOSED = 'exploration_closed';
    case EXPLORATION_CLOSED_BY_U_TURN = 'exploration_closed_by_u_turn';
    case EXPLORATION_CLOSED_EVERYONE_DEAD = 'exploration_closed_everyone_dead';
    case EXPLORATION_CLOSED_NO_SPACESUIT = 'exploration_closed_no_spacesuit';
    case EXPLORATION_CLOSED_RETURN_EVENT_MANKAROG = 'exploration_closed_return_event_mankarog';
    case EXPLORATION_CLOSED_RETURN_EVENT_SEISMIC_ACTIVITY = 'exploration_closed_return_event_seismic_activity';
    case EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY = 'exploration_closed_return_event_volcanic_activity';
    case EXPLORATION_STARTED_NO_SPACESUIT = 'exploration_started_no_spacesuit';
    case MISSION_ACCEPTED = 'mission_accepted';
    case MISSION_RECEIVED = 'mission_received';
    case MISSION_REJECTED = 'mission_rejected';
    case MISSION_SENT = 'mission_sent';
    case WELCOME_MUSH = 'welcome_mush';
    case ANNOUNCEMENT_RECEIVED = 'announcement_received';
    case ANNOUNCEMENT_CREATED = 'announcement_created';

    public function toString(): string
    {
        return $this->value;
    }
}
