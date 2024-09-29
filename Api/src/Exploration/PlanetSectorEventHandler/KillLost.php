<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;

final class KillLost extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_DESCRIPTIONS = 2;

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
        return PlanetSectorEvent::KILL_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $playerToKill = $this->randomService->getRandomPlayer($event->getExploration()->getDaedalus()->getLostPlayers());

        $this->playerService->killPlayer(
            player: $playerToKill,
            endReason: EndCauseEnum::mapEndCause($event->getTags()),
            time: $event->getTime()
        );

        $logParameters = $this->getLogParameters($event);
        $logParameters[$playerToKill->getLogKey()] = $playerToKill->getLogName();
        $logParameters['version'] = $this->randomService->random(1, self::NUMBER_OF_DESCRIPTIONS);

        return $this->createExplorationLog($event, $logParameters);
    }
}
