<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Player\Event\PlayerCycleEvent;
use Mush\Skill\Handler\LogisiticsExpertHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerCycleEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private LogisiticsExpertHandler $logisticsExpertHandler) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $this->logisticsExpertHandler->execute($event);
    }
}
