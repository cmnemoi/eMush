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

class Lost extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::LOST;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime, array $context = []): void
    {
        if ($status->getName() !== PlayerStatusEnum::LOST || !$statusHolder instanceof Player) {
            return;
        }

        $playerEvent = new PlayerEvent($statusHolder, $dateTime);
        $moralModifier = new Modifier();
        $moralModifier
            ->setDelta(-1)
            ->setTarget(ModifierTargetEnum::MORAL_POINT)
        ;

        $playerEvent
            ->setModifier($moralModifier)
            ->setReason(PlayerStatusEnum::LOST)
        ;

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }

    public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
