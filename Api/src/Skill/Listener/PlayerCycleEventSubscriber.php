<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Skill\Handler\LogisticsExpertHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerCycleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LogisticsExpertHandler $logisticsExpertHandler) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::LOW],
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $this->logisticsExpertHandler->execute($event);
    }
}
