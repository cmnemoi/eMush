<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\AddPlayerToExplorationTeamServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class FindLost extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_DESCRIPTIONS = 2;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        private AddPlayerToExplorationTeamServiceInterface $addPlayerToExplorationTeam,
        private StatusServiceInterface $statusService,
        private PlayerServiceInterface $playerService
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService);
    }

    public function getName(): string
    {
        return PlanetSectorEvent::FIND_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $daedalus = $event->getExploration()->getDaedalus();
        $exploration = $event->getExploration();
        $foundPlayer = $this->randomService->getRandomPlayer($exploration->getDaedalus()->getLostPlayers());
        if (!$exploration->getExplorators()->contains($foundPlayer)) {
            $this->addPlayerToExplorationTeam->execute($foundPlayer, $exploration);
        }

        $this->playerService->changePlace($foundPlayer, $daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET));
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $foundPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE
        );

        $logParameters = $this->getLogParameters($event);
        $logParameters[$foundPlayer->getLogKey()] = $foundPlayer->getLogName();
        $logParameters['version'] = $this->randomService->random(1, self::NUMBER_OF_DESCRIPTIONS);

        return $this->createExplorationLog($event, $logParameters);
    }
}
