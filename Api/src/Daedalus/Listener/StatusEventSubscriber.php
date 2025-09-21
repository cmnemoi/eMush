<?php

declare(strict_types=1);

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Service\DepressNeronServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private DepressNeronServiceInterface $depressNeron) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        match ($event->getStatusName()) {
            DaedalusStatusEnum::NERON_DEPRESSION => $this->dispatchNERONDepression($event),
            default => null,
        };
    }

    private function dispatchNERONDepression(StatusEvent $event): void
    {
        $neron = $event->getDaedalus()->getNeron();

        $this->depressNeron->execute(
            $neron,
            $event->getAuthor(),
            $event->getTags(),
            $event->getTime()
        );
    }
}
