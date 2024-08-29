<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
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
        $daedalus = $neron->getDaedalusInfo()->getDaedalus();

        $this->neronMessageService->createNeronMessage(
            messageKey: $neron->isInhibited() ? NeronMessageEnum::ACTIVATE_DMZ : NeronMessageEnum::DEACTIVATE_DMZ,
            daedalus: $daedalus,
            parameters: [],
            dateTime: $event->getTime()
        );
    }
}
