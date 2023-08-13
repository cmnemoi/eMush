<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;

class Septicemia extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::SEPTICEMIA;

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain {
        if (!$player->isAlive()) {
            return new EventChain([]);
        }

        $playerEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::INFECTION],
            $time
        );

        $playerEvent
            ->setPriority($priority)
            ->setEventName(PlayerEvent::DEATH_PLAYER)
        ;

        return new EventChain([$playerEvent]);
    }
}
