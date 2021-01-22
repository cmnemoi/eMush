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

        $playerEvent = new PlayerEvent($statusHolder, $dateTime);
        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(1)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
        ;

        $playerEvent
            ->setModifier($actionModifier)
            ->setReason(PlayerStatusEnum::LYING_DOWN)
        ;

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
