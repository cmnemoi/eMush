<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LyingDown extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::LYING_DOWN;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        if ($status->getName() !== PlayerStatusEnum::LYING_DOWN || !$statusHolder instanceof Player) {
            return;
        }

        $playerModifierEvent = new PlayerModifierEvent($statusHolder, 1, $dateTime);
        $playerModifierEvent->setReason(PlayerStatusEnum::LYING_DOWN);
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::ACTION_POINT_MODIFIER);
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
