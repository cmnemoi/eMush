<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

final class Disease extends AbstractPlanetSectorEventHandler
{
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        RoomLogServiceInterface $roomLogService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->diseaseCauseService = $diseaseCauseService;
        $this->roomLogService = $roomLogService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::DISEASE;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $diseasedPlayer = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostActiveExplorators());

        $disease = $this->diseaseCauseService->handleDiseaseForCause(
            cause: DiseaseCauseEnum::EXPLORATION,
            player: $diseasedPlayer
        );

        $this->roomLogService->createLog(
            logKey: LogEnum::DISEASE_BY_ALIEN_TRAVEL,
            place: $diseasedPlayer->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $diseasedPlayer,
            parameters: [
                'disease' => $disease->getName(),
                'is_player_mush' => $diseasedPlayer->isMush() ? 'true' : 'false',
            ],
            dateTime: $event->getTime(),
        );

        return $this->createExplorationLog($event, parameters: [$diseasedPlayer->getLogKey() => $diseasedPlayer->getLogName()]);
    }
}
