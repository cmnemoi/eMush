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

final class KillAll extends AbstractPlanetSectorEventHandler
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
        return PlanetSectorEvent::KILL_ALL;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        foreach ($event->getExploration()->getNotLostActiveExplorators() as $player) {
            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::mapEndCause($event->getTags()),
                time: $event->getTime()
            );
        }

        return $this->createExplorationLog($event, $this->getLogParameters($event));
    }
}
