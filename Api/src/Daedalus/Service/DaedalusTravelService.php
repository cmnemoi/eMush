<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Game\Service\EventServiceInterface;

final class DaedalusTravelService implements DaedalusTravelServiceInterface
{   

    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
    }

    public function turnDaedalusLeft(Daedalus $daedalus, array $reasons): Daedalus
    {   
        $daedalus->setOrientation(SpaceOrientationEnum::getCounterClockwiseOrientation($daedalus->getOrientation()));

        $this->persist([$daedalus]);

        $this->sendChangedOrientationEvent($daedalus, $reasons);
        
        return $daedalus;
    }

    public function turnDaedalusRight(Daedalus $daedalus, array $reasons): Daedalus
    {
        $daedalus->setOrientation(SpaceOrientationEnum::getClockwiseOrientation($daedalus->getOrientation()));

        $this->persist([$daedalus]);

        $this->sendChangedOrientationEvent($daedalus, $reasons);
        
        return $daedalus;
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    private function sendChangedOrientationEvent(Daedalus $daedalus, array $reasons): void
    {
        $daedalusEvent = new DaedalusEvent(
            $daedalus,
            tags: $reasons,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::CHANGED_ORIENTATION);
    }
}