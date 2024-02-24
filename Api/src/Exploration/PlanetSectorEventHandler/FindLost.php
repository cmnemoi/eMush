<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class FindLost extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_DESCRIPTIONS = 2;
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->statusService = $statusService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::FIND_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();
        $foundPlayer = $this->randomService->getRandomPlayer($exploration->getDaedalus()->getLostPlayers());
        if (!$exploration->getExplorators()->contains($foundPlayer)) {
            $this->addPlayerToExplorationTeam($foundPlayer, $exploration);
        }

        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LOST,
            holder: $foundPlayer,
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PRIVATE
        );

        $logParameters = [
            $foundPlayer->getLogKey() => $foundPlayer->getLogName(),
            'version' => $this->randomService->random(1, self::NUMBER_OF_DESCRIPTIONS),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }

    private function addPlayerToExplorationTeam(Player $player, Exploration $exploration): void
    {
        $exploration->addExplorator($player);
        $this->entityManager->persist($exploration);
    }
}
