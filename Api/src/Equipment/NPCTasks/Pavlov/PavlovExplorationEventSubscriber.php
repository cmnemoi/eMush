<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\Npc;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class listens to all exploration events, and applies Pavlov-relevant changes if the dog in question is around.
 * I didn't want to have to spread all of this behavior across a bunch of different subscribers...
 * Having it "quarantined off" here makes it easier to keep track of, and means all the pavlov-relevant code isn't making the main code harder to read when he isn't relevant.
 */
final class PavlovExplorationEventSubscriber implements EventSubscriberInterface
{
    public const int REVISIT_TRIGGER_CHANCE = 20;

    public function __construct(
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RoomLogServiceInterface $roomLogService,
        private ExplorationServiceInterface $explorationService,
        private RandomServiceInterface $randomService,
        private TranslationServiceInterface $translateService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::NORMAL],
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', EventPriorityEnum::HIGHEST],
            ExplorationEvent::EXPLORATION_NEW_CYCLE => ['onExplorationNewCycle', EventPriorityEnum::VERY_LOW],
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $startPlace = $event->getStartPlace();

        // if Pavlov is present, he joins the expedition
        if ($startPlace->getName() === RoomEnum::ICARUS_BAY && $startPlace->hasEquipmentByName(ItemEnum::PAVLOV)) {
            /** @var Npc $pavlov */
            $pavlov = $startPlace->getEquipmentByNameOrThrow(ItemEnum::PAVLOV);

            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $pavlov,
                newHolder: $daedalus->getPlanetPlace(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );

            $this->roomLogService->createLog(
                logKey: LogEnum::PAVLOV_JOINED_EXPEDITION,
                place: $daedalus->getPlanetPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                parameters: [],
                dateTime: $event->getTime(),
            );

            $pavlov->addDataToMemory('revisit_chance', self::REVISIT_TRIGGER_CHANCE);
        }
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        // Pavlov returns home no matter what, this function runs before handling equipment on planet
        if ($this->pavlovIsInExpedition($event)) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $event->getDaedalus()->getPlanetPlace()->getEquipmentByNameOrThrow(ItemEnum::PAVLOV),
                newHolder: $event->getStartPlace(),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

    public function onExplorationNewCycle(ExplorationEvent $event): void
    {
        if ($this->pavlovIsInExpedition($event)) {
            $exploration = $event->getExploration();
            $closedExploration = $event->getExploration()->getClosedExploration();

            // Exploration might have been closed early, if the "Back to Daedalus" event is triggered !
            if ($closedExploration->isExplorationFinished()) {
                return;
            }

            /** @var Npc $pavlov */
            $pavlov = $exploration->getDaedalus()->getPlanetPlace()->getEquipmentByNameOrThrow(ItemEnum::PAVLOV);

            if ($this->randomService->isSuccessful($pavlov->getIntFromMemory('revisit_chance'))) {
                $this->explorationService->selectNextSectorFromAlreadyVisitedSectors($exploration);
                $this->explorationService->persist([$exploration]);

                // sometimes, the exploration can fail to find a valid, already explored sector (e.g. the only visited sector is Oxygen). Don't print the log if so
                $nextSector = $exploration->getNextSector();
                if ($nextSector instanceof PlanetSector && $nextSector->isVisited()) {
                    $this->createPavlovSelectedRevisitLog($event);
                }
            }
        }
    }

    public function createPavlovSelectedRevisitLog(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();

        if ($exploration->getNextSector() === null) {
            return;
        }

        $nextSectorName = $this->translateService->translate(
            key: $exploration->getNextSectorOrThrow()->getName() . '.name',
            parameters: [],
            domain: 'planet',
            language: $exploration->getDaedalus()->getLanguage()
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::PAVLOV_TRIGGERED_SECTOR_REVISIT,
            place: $event->getDaedalus()->getPlanetPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            parameters: [
                'next_sector' => $nextSectorName,
            ],
            dateTime: $event->getTime(),
        );
    }

    public function pavlovIsInExpedition(ExplorationEvent $event): bool
    {
        return $event->getDaedalus()->getPlanetPlace()->hasEquipmentByName(ItemEnum::PAVLOV);
    }
}
