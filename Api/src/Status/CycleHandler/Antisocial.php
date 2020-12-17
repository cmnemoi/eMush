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

    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime)
    {
        if (!$object instanceof Status && $object->getName() !== PlayerStatusEnum::ANTISOCIAL) {
            return;
        }

        $player = $object->getPlayer();

        if ($player->getRoom()->getPlayers()->count() > 1) {
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

    public function handleNewDay($object, Daedalus $daedalus, \DateTime $dateTime)
    {
    }
}
