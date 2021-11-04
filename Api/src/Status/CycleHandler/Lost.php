<?php

namespace Mush\Status\CycleHandler;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
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
            PlayerVariableEnum::MORAL_POINT,
            -1,
            PlayerStatusEnum::LOST,
            $dateTime
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
