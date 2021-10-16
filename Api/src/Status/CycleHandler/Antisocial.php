<?php

namespace Mush\Status\CycleHandler;

use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEventInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Antisocial extends AbstractStatusCycleHandler
{
    protected string $name = PlayerStatusEnum::ANTISOCIAL;
    private EventDispatcherInterface $eventDispatcher;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(EventDispatcherInterface $eventDispatcher, RoomLogServiceInterface $roomLogService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->roomLogService = $roomLogService;
    }

    public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
        if ($status->getName() !== PlayerStatusEnum::ANTISOCIAL || !$statusHolder instanceof Player) {
            return;
        }

        if ($statusHolder->getPlace()->getPlayers()->getPlayerAlive()->count() > 1) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $statusHolder,
                -1,
                PlayerStatusEnum::ANTISOCIAL,
                $dateTime
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);

            $this->roomLogService->createLog(
                LogEnum::ANTISOCIAL_MORALE_LOSS,
                $statusHolder->getPlace(),
                VisibilityEnum::PRIVATE,
                'eventLog',
                $statusHolder,
            );
        }
    }

    public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void
    {
    }
}
