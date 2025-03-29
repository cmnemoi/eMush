<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Event\AbstractNPCEvent;
use Mush\Equipment\Event\AnnoyCatEvent;
use Mush\Equipment\Event\NPCMovedEvent;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NPCEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            NPCMovedEvent::class => 'onNPCMoved',
            AnnoyCatEvent::class => 'onPavlovAnnoyCat',
        ];
    }

    public function onNPCMoved(NPCMovedEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::NPC_EXITED_ROOM,
            $event->getOldRoom(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );

        $this->roomLogService->createLog(
            LogEnum::NPC_ENTERED_ROOM,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    public function onPavlovAnnoyCat(AnnoyCatEvent $event): void
    {
        $this->roomLogService->createLog(
            LogEnum::DOG_BOTHER_CAT,
            $event->getPlace(),
            $event->getVisibility(),
            'event_log',
            null,
            $this->getLogParameters($event),
            $event->getTime()
        );
    }

    private function getLogParameters(AbstractNPCEvent $event): array
    {
        return $event->getLogParameters();
    }
}
