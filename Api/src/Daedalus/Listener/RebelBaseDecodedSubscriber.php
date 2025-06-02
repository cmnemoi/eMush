<?php

namespace Mush\Daedalus\Listener;

use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RebelBaseDecodedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RebelBaseDecodedEvent::class => 'onRebelBaseDecoded',
        ];
    }

    public function onRebelBaseDecoded(RebelBaseDecodedEvent $event): void
    {
        $event->getDaedalusStatistics()->changeRebelBasesContacted(1);

        $this->daedalusRepository->save($event->getDaedalus());
    }
}
