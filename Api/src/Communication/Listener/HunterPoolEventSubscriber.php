<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageService;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HunterPoolEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NeronMessageService $neronMessageService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        if ($event->shouldNotGenerateNeronAnnouncement()) {
            return;
        }

        $daedalus = $event->getDaedalus();
        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::HUNTER_ARRIVAL,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime(),
        );
    }
}
