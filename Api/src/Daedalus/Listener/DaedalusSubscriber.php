<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService
    ) {
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::FINISH_DAEDALUS => 'onDaedalusFinish',
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', EventPriorityEnum::LOW],
        ];
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

        if (!$endCause) {
            throw new \LogicException('daedalus should end with a reason');
        }

        $this->daedalusService->endDaedalus($daedalus, $endCause, $event->getTime());
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalusInfo = $daedalus->getDaedalusInfo();

        // Assign Titles
        $this->daedalusService->attributeTitles($daedalus, $event->getTime());

        // Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus, $event->getTime());

        $daedalus->setFilledAt(new \DateTime());
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalusService->persist($daedalus);
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $this->daedalusService->startDaedalus($daedalus);
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $daedalus->setCombustionChamberFuel(0);

        $this->daedalusService->persist($daedalus);
    }
}
