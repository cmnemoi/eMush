<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;

class Septicemia extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::SEPTICEMIA;

    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        if (!$player->isAlive()) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::INFECTION],
            $time
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
    }
}
