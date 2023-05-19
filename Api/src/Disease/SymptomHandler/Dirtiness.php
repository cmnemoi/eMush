<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class Dirtiness extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::DIRTINESS;
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public function applyEffects(string $symptomName, Player $player, \DateTime $time): void
    {
        if ($symptomName !== SymptomEnum::VOMITING) {
            return;
        }

        $this->handleDirty($player, [$symptomName], $time);
    }

    private function handleDirty(Player $player, array $reasons, \DateTime $time): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $reasons,
            $time
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
