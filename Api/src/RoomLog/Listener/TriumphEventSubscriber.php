<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Triumph\Event\TriumphChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TriumphEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            TriumphChangedEvent::class => 'onTriumphChanged',
        ];
    }

    public function onTriumphChanged(TriumphChangedEvent $event): void
    {
        $this->roomLogService->createLog(
            logKey: $event->getLogKey(),
            place: $event->getPlace(),
            visibility: $event->getVisibility(),
            type: 'triumph',
            player: $event->getPlayer(),
            dateTime: $event->getTime(),
            parameters: [
                'quantity' => $event->getQuantity(),
            ],
        );
    }
}
