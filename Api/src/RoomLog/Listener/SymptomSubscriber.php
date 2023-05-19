<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Event\SymptomEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymptomSubscriber implements EventSubscriberInterface
{
    private const SYMPTOM_LOG_MAP = [
        SymptomEnum::BITING => SymptomEnum::BITING,
        SymptomEnum::BREAKOUTS => SymptomEnum::BREAKOUTS,
        SymptomEnum::CAT_ALLERGY => SymptomEnum::CAT_ALLERGY,
        SymptomEnum::DIRTINESS => SymptomEnum::DIRTINESS,
        SymptomEnum::DROOLING => SymptomEnum::DROOLING,
        SymptomEnum::FEAR_OF_CATS => SymptomEnum::FEAR_OF_CATS,
        SymptomEnum::FOAMING_MOUTH => SymptomEnum::FOAMING_MOUTH,
        SymptomEnum::SNEEZING => SymptomEnum::SNEEZING,
        SymptomEnum::VOMITING => SymptomEnum::VOMITING,
    ];


    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SymptomEvent::TRIGGER_SYMPTOM => 'onTriggerSymptom',
        ];
    }

    public function onTriggerSymptom(SymptomEvent $event): void
    {
        $symptomName = $event->getSymptomName();

        $logKey = $event->mapLog(self::SYMPTOM_LOG_MAP);

        if ($logKey === null) {
            return;
        }

        $player = $event->getAuthor();
        $time = $event->getTime();

        $this->createSymptomLog($logKey, $player, $time, $event->getVisibility(), $event->getLogParameters());

        if ($symptomName === SymptomEnum::FEAR_OF_CATS) {
            $this->createSymptomLog($symptomName . '_notif', $player, $time, VisibilityEnum::PRIVATE, $event->getLogParameters());
        }
    }

    private function createSymptomLog(string $symptomLogKey,
                                      Player $player,
                                      \DateTime $date,
                                      string $visibility = VisibilityEnum::PUBLIC,
                                      array $logParameters = []): void
    {
        $this->roomLogService->createLog(
            $symptomLogKey,
            $player->getPlace(),
            $visibility,
            'event_log',
            $player,
            $logParameters,
            $date
        );
    }
}
