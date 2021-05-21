<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
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
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::CHANGE_HULL => 'onChangeHull',
            DaedalusEvent::CHANGE_OXYGEN => 'onChangeOxygen',
        ];
    }

    public function onDaedalusEnd(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $reason = $event->getReason();

        if (!$reason) {
            throw new \LogicException('daedalus should end with a reason');
        }

        $this->daedalusService->killRemainingPlayers($daedalus, $reason, $event->getTime());

        // @TODO: create logs
        // @TODO: remove all fire and charged statuses

        $daedalus->getPlaces()->map(static function ($room) {
            /** @var \Mush\Place\Entity\Place $room */
            foreach ($room->getStatuses() as $status) {
                $room->removeStatus($status);
            }
        });

        $daedalus->setFinishedAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::FINISHED);
        $this->daedalusService->persist($daedalus);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        //@TODO give titles

        //Chose alpha Mushs
        $this->daedalusService->selectAlphaMush($daedalus, $event->getTime());

        $daedalus->setFilledAt(new \DateTime());
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalusService->persist($daedalus);
    }

    public function onChangeHull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $date = $event->getTime();

        $change = $event->getQuantity();
        if ($change === null) {
            throw new \LogicException('quantity should be provided');
        }

        $this->daedalusService->changeHull($daedalus, $change, $date);
    }

    public function onChangeOxygen(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $change = $event->getQuantity();
        if ($change === null) {
            throw new \LogicException('quantity should be provided');
        }

        $this->daedalusService->changeOxygenLevel($daedalus, $change);
    }

    public function onChangeFuel(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $change = $event->getQuantity();
        if ($change === null) {
            throw new \LogicException('quantity should be provided');
        }

        $this->daedalusService->changeFuelLevel($daedalus, $change);
    }
}
