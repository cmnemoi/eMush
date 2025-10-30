<?php

declare(strict_types=1);

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;

final class CommanderMissionAcceptedEvent extends AbstractGameEvent
{
    public function __construct(
        private readonly CommanderMission $mission,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getCommander(): Player
    {
        return $this->mission->getCommander();
    }
}
