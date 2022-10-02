<?php

namespace Mush\Place\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Service\PlaceServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitSubscriber implements EventSubscriberInterface
{
    private PlaceServiceInterface $placeService;

    public function __construct(
        PlaceServiceInterface $placeService
    ) {
        $this->placeService = $placeService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusConfig = $event->getDaedalusConfig();

        /** @var PlaceConfig $placeConfig */
        foreach ($daedalusConfig->getPlaceConfigs() as $placeConfig) {
            $place = $this->placeService->createPlace(
                $placeConfig,
                $daedalus,
                $event->getReasons()[0],
                $event->getTime()
            );

            $daedalus->addPlace($place);
        }
    }
}
