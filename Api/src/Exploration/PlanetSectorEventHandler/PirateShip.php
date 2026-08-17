<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PirateShip extends AbstractPlanetSectorEventHandler
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
    }

    public function getName(): string
    {
        return PlanetSectorEvent::PIRATE_SHIP;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        // we create the status that will contain the information for where the treasure is
        $status = $this->statusService->createOrIncrementChargeStatus(
            DaedalusStatusEnum::TREASURE_SECTOR,
            $event->getDaedalus(),
        );

        $this->statusService->updateCharge(
            $status,
            $this->randomService->random(0, $status->getMaxChargeOrThrow()),
            $event->getTags(),
            $event->getTime(),
            VariableEventInterface::SET_VALUE
        );

        $logParameters = $this->getLogParameters($event);
        $logParameters['treasure_sector'] = $status->getCharge();

        return $this->createExplorationLog($event, $logParameters);
    }
}
