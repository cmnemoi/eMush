<?php

namespace Mush\RoomLog\Listener;

use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', 10],
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
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

    private function createEventLog(string $logKey, PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $logParameters = $event->getLogParameters();
        $language = $player->getDaedalus()->getLanguage();

        if ($logKey === LogEnum::DEATH) {
            $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

            if ($endCause === null) {
                throw new \LogicException('Player should die with a reason');
            }

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
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            $logParameters,
            $event->getTime()
        );
    }
}
