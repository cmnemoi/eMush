<?php

declare(strict_types=1);

namespace Mush\Communications\Listener;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Communications\Service\KillAllRebelBaseContactsService;
use Mush\Communications\Service\KillExpiredRebelBaseContactsService;
use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Communications\Service\TriggerNextRebelBaseContactService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus,
        private KillLinkWithSolService $killLinkWithSol,
        private LinkWithSolRepositoryInterface $linkWithSolRepository,
        private KillAllRebelBaseContactsService $killAllRebelBaseContacts,
        private KillExpiredRebelBaseContactsService $killExpiredRebelBaseContacts,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private NeronVersionRepositoryInterface $neronVersionRepository,
        private TriggerNextRebelBaseContactService $triggerNextRebelBaseContact,
        private XylophRepositoryInterface $xylophRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::REBEL_BASES],
            DaedalusEvent::DELETE_DAEDALUS => 'onDaedalusDelete',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::START_DAEDALUS => 'onDaedalusStart',
            DaedalusEvent::TRAVEL_LAUNCHED => 'onDaedalusTravelLaunched',
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $this->triggerNextRebelBaseContact->execute($event->getDaedalusId(), $event->getTime());
        $this->killExpiredRebelBaseContacts->execute($event->getDaedalusId(), $event->getTime());
        $this->killLinkWithSol->execute($event->getDaedalusId(), successRate: $event->getLinkWithSolCycleKillChance());
    }

    public function onDaedalusDelete(DaedalusEvent $event): void
    {
        $this->linkWithSolRepository->deleteByDaedalusId($event->getDaedalusId());
        $this->neronVersionRepository->deleteByDaedalusId($event->getDaedalusId());
        $this->rebelBaseRepository->deleteAllByDaedalusId($event->getDaedalusId());
        $this->xylophRepository->deleteAllByDaedalusId($event->getDaedalusId());
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $this->triggerNextRebelBaseContact->execute($event->getDaedalusId(), $event->getTime());
    }

    public function onDaedalusStart(DaedalusEvent $event): void
    {
        $this->createLinkWithSolForDaedalus->execute($event->getDaedalusId());
        $this->neronVersionRepository->save(new NeronVersion($event->getDaedalusId()));
        $this->createRebelBases($event->getDaedalus());
        $this->createXylophDatabases($event->getDaedalus());
    }

    public function onDaedalusTravelLaunched(DaedalusEvent $event): void
    {
        $this->killAllRebelBaseContacts->execute($event->getDaedalusId());
    }

    private function createRebelBases(Daedalus $daedalus): void
    {
        foreach ($daedalus->getGameConfig()->getRebelBaseConfigs() as $rebelBaseConfig) {
            $this->rebelBaseRepository->save(new RebelBase($rebelBaseConfig, $daedalus->getId()));
        }
    }

    private function createXylophDatabases(Daedalus $daedalus): void
    {
        foreach ($daedalus->getGameConfig()->getXylophConfigs() as $xylophConfig) {
            $this->xylophRepository->save(new XylophEntry($xylophConfig, $daedalus->getId()));
        }
    }
}
