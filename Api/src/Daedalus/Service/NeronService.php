<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\NeronEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

final class NeronService implements NeronServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;

    public function __construct(EntityManagerInterface $entityManager, EventServiceInterface $eventService)
    {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
    }

    public function changeCpuPriority(Neron $neron, string $cpuPriority, array $reasons = [], ?Player $author = null): void
    {
        $reasons['oldCpuPriority'] = $neron->getCpuPriority();

        $neron->setCpuPriority($cpuPriority);

        $neronEvent = new NeronEvent($neron, $reasons, new \DateTime());
        $neronEvent->setAuthor($author);
        $this->eventService->callEvent($neronEvent, NeronEvent::CPU_PRIORITY_CHANGED);

        $this->persist([$neron]);
    }

    public function toggleInhibition(Neron $neron): void
    {
        $neron->toggleInhibition();
        $this->persist([$neron]);

        $neronEvent = new NeronEvent($neron);
        $this->eventService->callEvent($neronEvent, NeronEvent::INHIBITION_TOGGLED);
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
