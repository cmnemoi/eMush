<?php

namespace Mush\Status\CycleHandler;

use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Lost extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::LOST;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if ($status->getName() !== PlayerStatusEnum::LOST || !$statusHolder instanceof Player) {
            return;
        }

        $playerModifierEvent = new PlayerModifierEvent(
            $statusHolder,
            -1,
            PlayerStatusEnum::LOST,
            $dateTime
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
