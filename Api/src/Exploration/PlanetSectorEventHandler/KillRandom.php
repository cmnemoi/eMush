<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;

final class KillRandom extends AbstractPlanetSectorEventHandler
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
    }

    public function getName(): string
    {
        return PlanetSectorEvent::KILL_RANDOM;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $playerToKill = $this->drawPlayerToKill($event);

        $this->playerService->killPlayer(
            player: $playerToKill,
            endReason: EndCauseEnum::mapEndCause($event->getTags()),
            time: $event->getTime()
        );

        $logParameters = $this->getLogParameters($event);
        $logParameters[$playerToKill->getLogKey()] = $playerToKill->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }

    private function drawPlayerToKill(PlanetSectorEvent $event): Player
    {
        $exploration = $event->getExploration();
        $nonSurvivalists = $exploration->getActiveNonSurvivalistExplorators();
        if ($nonSurvivalists->count() > 0) {
            return $this->randomService->getRandomPlayer($nonSurvivalists);
        }

        return $this->randomService->getRandomPlayer($exploration->getNotLostActiveExplorators());
    }
}
