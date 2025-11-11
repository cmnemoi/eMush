<?php

declare(strict_types=1);

namespace Mush\MetaGame\Listener;

use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ModerationServiceInterface $moderationService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::END_PLAYER => 'onEndPlayer',
        ];
    }

    public function onEndPlayer(PlayerEvent $event)
    {
        $this->moderationService->triggerUserBans($event->getPlayer()->getUser());
    }
}
