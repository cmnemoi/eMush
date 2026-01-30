<?php

declare(strict_types=1);

namespace Mush\RoomLog\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;
    private TranslationServiceInterface $translateService;

    public function __construct(RoomLogServiceInterface $roomLogService, TranslationServiceInterface $translateService)
    {
        $this->roomLogService = $roomLogService;
        $this->translateService = $translateService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', EventPriorityEnum::LOWEST],
            ExplorationEvent::EXPLORATION_NEW_CYCLE => ['distributeSpoilers', EventPriorityEnum::LOWEST],
            ExplorationEvent::EXPLORATION_STARTED => ['distributeSpoilers', EventPriorityEnum::LOWEST],
        ];
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $explorators = $exploration->getExplorators();

        $explorationUrl = '/expPerma/' . $exploration->getClosedExploration()->getId();
        $here = $this->translateService->translate(
            key: 'here',
            parameters: [],
            domain: 'misc',
            language: $exploration->getDaedalus()->getLanguage()
        );

        if ($event->hasTag(ExplorationEvent::ALL_EXPLORATORS_STUCKED)) {
            foreach ($explorators as $explorator) {
                $this->roomLogService->createLog(
                    logKey: LogEnum::ALL_EXPLORATORS_STUCKED,
                    place: $explorator->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    type: 'event_log',
                    player: $explorator,
                );
            }
        } elseif (
            $event->hasAnyTag([
                ExplorationEvent::ALL_EXPLORATORS_ARE_DEAD,
                DaedalusEvent::FINISH_DAEDALUS,
            ]) && $event->doesNotHaveAnyTag([EndCauseEnum::EDEN, EndCauseEnum::SOL_RETURN])
        ) {
            foreach ($explorators as $explorator) {
                $this->roomLogService->createLog(
                    logKey: LogEnum::ALL_EXPLORATORS_DEAD,
                    place: $explorator->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    type: 'event_log',
                    player: $explorator,
                    parameters: [
                        'exploration_link' => "<a href='{$explorationUrl}'>" . strtoupper($here) . '</a>',
                    ]
                );
            }
        } else {
            foreach ($explorators as $explorator) {
                $this->roomLogService->createLog(
                    logKey: LogEnum::EXPLORATION_FINISHED,
                    place: $explorator->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    type: 'event_log',
                    player: $explorator,
                    parameters: [
                        'exploration_link' => "<a href='{$explorationUrl}'>" . strtoupper($here) . '</a>',
                    ]
                );
            }
        }
    }

    public function distributeSpoilers(ExplorationEvent $event): void
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

        foreach ($exploration->getPlayersWhoCanSeeNextSector() as $previewer) {
            if ($previewer->doesNotHaveStatus(PlayerStatusEnum::LOST)) {
                $this->roomLogService->createLog(
                    logKey: LogEnum::EXPLORATION_EVENT_PREVIEW,
                    place: $previewer->getPlace(),
                    visibility: VisibilityEnum::PRIVATE,
                    player: $previewer,
                    type: 'event_log',
                    parameters: [
                        'next_sector' => $nextSectorName,
                    ],
                    dateTime: $event->getTime(),
                );
            }
        }
    }
}
