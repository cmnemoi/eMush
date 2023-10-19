<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;

    public function __construct(
        PlanetServiceInterface $planetService,
        StatusServiceInterface $statusService
    ) {
        $this->planetService = $planetService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $daedalus,
            tags: $event->getTags(),
            time: new \DateTime(),
        );

        if ($this->planetService->findOneByDaedalusDestination($daedalus) !== null) {
            $this->statusService->createStatusFromName(
                statusName: DaedalusStatusEnum::IN_ORBIT,
                holder: $daedalus,
                tags: $event->getTags(),
                time: new \DateTime(),
            );
        }
    }
}
