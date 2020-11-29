<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Antisocial extends AbstractCycleHandler
{
    protected string $name = PlayerStatusEnum::ANTISOCIAL;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle($status, Daedalus $daedalus, \DateTime $dateTime)
    {
        if (!$status instanceof Status && $status->getName() !== PlayerStatusEnum::ANTISOCIAL) {
            return;
        }

        $player = $status->getPlayer();

        if ($player->getRoom()->getPlayers()->count() > 2) {
            $playerEvent = new PlayerEvent($player, $dateTime);
            $actionModifier = new ActionModifier();
            $actionModifier->setMoralPointModifier(-1);
            $playerEvent
                ->setActionModifier($actionModifier)
                ->setReason(PlayerStatusEnum::ANTISOCIAL)
            ;

            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
    }

    public function handleNewDay($status, Daedalus $daedalus, \DateTime $dateTime)
    {
    }
}
