<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusCycleSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusCycleEvent::STATUS_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(StatusCycleEvent $event): void
    {
        if (!($status = $event->getStatus())) {
            return;
        }

        if ($status->getName() === PlayerStatusEnum::ANTISOCIAL) {
            $player = $event->getHolder();

            if (!$player instanceof Player) {
                throw new UnexpectedTypeException($player, Player::class);
            }

            $this->roomLogService->createLog(
                LogEnum::ANTISOCIAL_MORALE_LOSS,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'eventLog',
                $player,
            );
        }
    }
}
