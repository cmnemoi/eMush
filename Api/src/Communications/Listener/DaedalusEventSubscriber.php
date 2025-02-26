<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Communications\Service\TriggerNextRebelBaseContactService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\CycleServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus,
        private CycleServiceInterface $cycleService,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private NeronVersionRepositoryInterface $neronVersionRepository,
        private TriggerNextRebelBaseContactService $triggerNextRebelBaseContact,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::REBEL_BASES],
            DaedalusEvent::DELETE_DAEDALUS => 'onDaedalusDelete',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $mostRecentContactingRebelBase = $this->rebelBaseRepository->findMostRecentContactingRebelBase($daedalus->getId());
        if (!$mostRecentContactingRebelBase) {
            return;
        }

        if ($this->numberOfCyclesSinceContact($mostRecentContactingRebelBase, $event) >= $this->numberOfCyclesBeforeNextRebelBaseContact($daedalus)) {
            $this->triggerNextRebelBaseContact->execute($daedalus->getId());
        }
    }

    public function onDaedalusDelete(DaedalusEvent $event): void
    {
        $this->linkWithSolRepository->deleteByDaedalusId($event->getDaedalus()->getId());
        $this->neronVersionRepository->deleteByDaedalusId($event->getDaedalus()->getId());
        $this->rebelBaseRepository->deleteAllByDaedalusId($event->getDaedalus()->getId());
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $this->triggerNextRebelBaseContact->execute($event->getDaedalus()->getId());
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $this->createLinkWithSolForDaedalus->execute($event->getDaedalus()->getId());
        $this->neronVersionRepository->save(new NeronVersion($event->getDaedalus()->getId()));
        $this->createRebelBases($event->getDaedalus());
    }

    private function createRebelBases(Daedalus $daedalus): void
    {
        foreach ($daedalus->getDaedalusInfo()->getGameConfig()->getRebelBaseConfigs() as $rebelBaseConfig) {
            $this->rebelBaseRepository->save(new RebelBase($rebelBaseConfig, $daedalus->getId()));
        }
    }

    private function numberOfCyclesSinceContact(RebelBase $rebelBase, DaedalusCycleEvent $event): int
    {
        return $this->cycleService->getNumberOfCycleElapsed(
            start: $rebelBase->getContactStartDateOrThrow(),
            end: $event->getTime(),
            daedalusInfo: $event->getDaedalus()->getDaedalusInfo(),
        );
    }

    private function numberOfCyclesBeforeNextRebelBaseContact(Daedalus $daedalus): int
    {
        return $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getNumberOfCyclesBeforeNextRebelBaseContact();
    }
}
