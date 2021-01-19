<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Starving extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::STARVING;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        if ($status->getName() !== PlayerStatusEnum::STARVING || !$statusHolder instanceof Player) {
            return;
        }

        $playerEvent = new PlayerEvent($statusHolder, $dateTime);

        $healthModifier = new Modifier();
        $healthModifier
            ->setDelta(-1)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;

        $playerEvent
            ->setModifier($healthModifier)
            ->setReason(PlayerStatusEnum::STARVING)
        ;

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
