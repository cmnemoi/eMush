<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class Dirtiness extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::DIRTINESS;

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return new EventChain();
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $tags,
            $time
        );
        $statusEvent
            ->setPriority($priority)
            ->setEventName(StatusEvent::STATUS_APPLIED)
        ;

        return new EventChain([$statusEvent]);
    }
}
