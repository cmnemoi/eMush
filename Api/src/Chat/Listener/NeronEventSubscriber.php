<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Event\NeronEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NeronEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private NeronMessageServiceInterface $neronMessageService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            NeronEvent::INHIBITION_TOGGLED => 'onInhibitionToggled',
        ];
    }

    public function onInhibitionToggled(NeronEvent $event): void
    {
        $neron = $event->getNeron();
        $daedalus = $event->getDaedalus();

        $this->neronMessageService->createNeronMessage(
            messageKey: $neron->isInhibited() ? NeronMessageEnum::ACTIVATE_DMZ : NeronMessageEnum::DEACTIVATE_DMZ,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime()
        );
    }
}
