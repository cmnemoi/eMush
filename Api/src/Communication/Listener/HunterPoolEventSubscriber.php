<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HunterPoolEventSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
    ) {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterPoolEvent::UNPOOL_HUNTERS => 'onUnpoolHunters',
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        if ($daedalus->getAttackingHunters()->isEmpty()) {
            return;
        }

        $this->neronMessageService->createNeronMessage(
            messageKey: NeronMessageEnum::HUNTER_ARRIVAL,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime(),
        );
    }
}
