<?php

namespace Mush\RoomLog\Listener;

use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        TranslationServiceInterface $translationService,
    ) {
        $this->roomLogService = $roomLogService;
        $this->translationService = $translationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', 10],
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerChangedPlaceEvent::class => 'onPlayerChangedPlace',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::AWAKEN, $event);
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::DEATH, $event);
    }

    public function onMetalPlate(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::METAL_PLATE, $event);
    }

    public function onPlayerChangedPlace(PlayerChangedPlaceEvent $event): void
    {
        if ($event->getPlace()->isNotARoom() || $event->getOldPlace()->isNotARoom()) {
            return;
        }

        $this->createMoveLogFromEvent($event, logKey: ActionLogEnum::EXIT_ROOM);
        $this->createMoveLogFromEvent($event, logKey: ActionLogEnum::ENTER_ROOM);
    }

    private function createMoveLogFromEvent(PlayerChangedPlaceEvent $event, string $logKey): void
    {
        $player = $event->getPlayer();
        $placeForTranslation = $logKey === ActionLogEnum::EXIT_ROOM ? $event->getPlace() : $event->getOldPlace();
        $logPlace = $logKey === ActionLogEnum::EXIT_ROOM ? $event->getOldPlace() : $event->getPlace();
        $prepositionKey = $logKey === ActionLogEnum::EXIT_ROOM ? 'exit_loc_prep' : 'enter_loc_prep';

        $translatedPreposition = $this->translationService->translate(
            "{$placeForTranslation->getLogName()}.{$prepositionKey}",
            [],
            'rooms',
            $player->getLanguage()
        );

        $this->roomLogService->createLog(
            $logKey,
            $logPlace,
            VisibilityEnum::PUBLIC,
            'actions_log',
            $player,
            [
                $player->getLogKey() => $player->getAnonymousKeyOrLogName(),
                $placeForTranslation->getLogKey() => $placeForTranslation->getLogName(),
                $prepositionKey => $translatedPreposition,
            ],
            $event->getTime(),
        );
    }

    private function createEventLog(string $logKey, PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $logParameters = $event->getLogParameters();
        $language = $player->getDaedalus()->getLanguage();
        $isNotDeathEndCause = false;

        if ($logKey === LogEnum::DEATH) {
            $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

            if ($endCause === null) {
                throw new \LogicException('Player should die with a reason');
            }

            $isNotDeathEndCause = EndCauseEnum::isNotDeathEndCause($endCause);
            $logParameters[LanguageEnum::END_CAUSE] = $this->translationService->translate(
                $endCause,
                [],
                'end_cause',
                $language
            );
        }

        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $isNotDeathEndCause ? VisibilityEnum::HIDDEN : VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            $logParameters,
            $event->getTime()
        );
    }
}
