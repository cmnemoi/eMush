<?php

declare(strict_types=1);

namespace Mush\Player\Enum;

enum PlayerNotificationEnum: string
{
    case DROPPED_CRITICAL_ITEM = 'dropped_critical_item';
    case EXPLORATION_CLOSED_BY_U_TURN = 'exploration_closed_by_u_turn';
    case MISSION_ACCEPTED = 'mission_accepted';
    case MISSION_RECEIVED = 'mission_received';
    case MISSION_REJECTED = 'mission_rejected';
    case MISSION_SENT = 'mission_sent';

    public function toString(): string
    {
        return $this->value;
    }
}
