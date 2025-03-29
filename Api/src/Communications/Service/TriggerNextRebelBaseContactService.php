<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Event\RebelBaseStartedContactEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;

/**
 * @psalm-suppress PossiblyNullArgument
 */
final readonly class TriggerNextRebelBaseContactService
{
    public function __construct(
        private CycleServiceInterface $cycleService,
        private EventServiceInterface $eventService,
        private DaedalusRepositoryInterface $daedalusRepository,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {}

    public function execute(int $daedalusId, \DateTime $time): void
    {
        $nextContactingRebelBase = $this->rebelBaseRepository->findNextContactingRebelBase($daedalusId);
        if ($this->allRebelBasesAlreadyContacted($nextContactingRebelBase)) {
            return;
        }

        if ($this->shouldTriggerContact($daedalusId, $time)) {
            $this->triggerRebelBaseContact($nextContactingRebelBase, $time);
        }
    }

    private function shouldTriggerContact(int $daedalusId, \DateTime $time): bool
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($daedalusId);
        if ($daedalus->isFilling()) {
            return false;
        }

        $rebelBase = $this->rebelBaseRepository->findMostRecentContactingRebelBase($daedalusId);
        if ($this->isFirstContact($rebelBase)) {
            return true;
        }

        return $this->hasEnoughCyclesPassedSinceLastContact($rebelBase, $daedalus, $time);
    }

    private function hasEnoughCyclesPassedSinceLastContact(RebelBase $rebelBase, Daedalus $daedalus, \DateTime $time): bool
    {
        $cyclesSinceLastContact = $this->numberOfCyclesSinceContact($rebelBase, $daedalus, $time);

        return $cyclesSinceLastContact >= $daedalus->getDaedalusConfig()->getNumberOfCyclesBeforeNextRebelBaseContact();
    }

    private function triggerRebelBaseContact(RebelBase $rebelBase, \DateTime $contactDate): void
    {
        $rebelBase->triggerContact($contactDate);
        $this->rebelBaseRepository->save($rebelBase);

        $this->eventService->callEvent(
            event: new RebelBaseStartedContactEvent($rebelBase->getDaedalusId()),
            name: RebelBaseStartedContactEvent::class,
        );
    }

    private function numberOfCyclesSinceContact(RebelBase $rebelBase, Daedalus $daedalus, \DateTime $time): int
    {
        return $this->cycleService->getNumberOfCycleElapsed(
            start: $rebelBase->getContactStartDateOrThrow(),
            end: $time,
            daedalusInfo: $daedalus->getDaedalusInfo(),
        );
    }

    private function isFirstContact(?RebelBase $mostRecentContactingRebelBase): bool
    {
        return $mostRecentContactingRebelBase === null;
    }

    private function allRebelBasesAlreadyContacted(?RebelBase $nextContactingRebelBase): bool
    {
        return $nextContactingRebelBase === null;
    }
}
