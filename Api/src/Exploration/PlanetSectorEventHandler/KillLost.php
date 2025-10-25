<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;

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
        $exploration = $event->getExploration();
        if ($exploration->hasAnActiveTracker()) {
            $event->addTag(SkillEnum::TRACKER->toString());

            return $this->dispatchNonKillEvent($event);
        }

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

    private function dispatchNonKillEvent(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();
        $newPlanetSectorEvents = $this->getPlanetSectorEventsWithoutKillOne($event);
        $eventConfigToDispatch = $this->drawPlanetSectorEventConfigToDispatch($newPlanetSectorEvents);

        $this->dispatchPlanetSectorEvent($eventConfigToDispatch, $event);

        return new ExplorationLog($exploration->getClosedExploration());
    }

    private function getPlanetSectorEventsWithoutKillOne(PlanetSectorEvent $event): ProbaCollection
    {
        $sectorEvents = clone $event->getPlanetSector()->getExplorationEvents();
        $sectorEvents->remove($event->getKey());

        return $sectorEvents;
    }

    private function drawPlanetSectorEventConfigToDispatch(ProbaCollection $events): PlanetSectorEventConfig
    {
        $newEventKey = (string) $this->randomService->getSingleRandomElementFromProbaCollection($events);

        return $this->getPlanetSectorEventConfigByKey($newEventKey);
    }

    private function getPlanetSectorEventConfigByKey(string $key): PlanetSectorEventConfig
    {
        /** @var ?PlanetSectorEventConfig $eventConfig */
        $eventConfig = $this->entityManager->getRepository(PlanetSectorEventConfig::class)->findOneByName($key);
        if (!$eventConfig) {
            throw new \RuntimeException('PlanetSectorEventConfig not found for event ' . $key);
        }

        return $eventConfig;
    }

    private function dispatchPlanetSectorEvent(PlanetSectorEventConfig $eventConfig, PlanetSectorEvent $event): void
    {
        $planetSectorEvent = new PlanetSectorEvent(
            $event->getPlanetSector(),
            $eventConfig,
            $event->getTags(),
            $event->getTime(),
            $event->getVisibility()
        );
        $this->eventService->callEvent($planetSectorEvent, PlanetSectorEvent::PLANET_SECTOR_EVENT);
    }
}
