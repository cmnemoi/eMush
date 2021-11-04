<?php

namespace Mush\Status\CycleHandler;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
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
            $playerModifierEvent = new PlayerModifierEvent(
                $statusHolder,
                PlayerVariableEnum::MORAL_POINT,
                -1,
                PlayerStatusEnum::ANTISOCIAL,
                $dateTime
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

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
