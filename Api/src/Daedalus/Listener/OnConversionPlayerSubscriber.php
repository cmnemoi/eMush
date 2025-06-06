<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnConversionPlayerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $event->getDaedalusStatistics()->changeMushAmount(1);

        $this->daedalusRepository->save($event->getDaedalus());
    }
}
