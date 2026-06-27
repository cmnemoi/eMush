<?php

declare(strict_types=1);

namespace Mush\Modifier\ModifierHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Event\ExplorationSelectionEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Modifier\Entity\Config\ExplorationEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierStrategyEnum;

class ExplorationSectorSelectionModifier extends AbstractModifierHandler
{
    protected string $name = ModifierStrategyEnum::EXPLORATION_SECTOR_SELECTION_MODIFIER;

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var ExplorationEventModifierConfig $modifierConfig */
        $modifierConfig = $modifier->getModifierConfig();
        $initialEvent = $events->getInitialEvent();

        // if the initial event do not exist anymore
        if (!$initialEvent instanceof ExplorationSelectionEvent) {
            return $events;
        }

        // if the event already have been modified no need for extra changes
        if ($initialEvent->isModified()) {
            return $this->addModifierEvent($events, $modifier, $tags, $time);
        }

        switch ($modifierConfig->getAction()) {
            case ExplorationEventModifierConfig::ADD:
                $this->addEvent($initialEvent, $modifierConfig);

                break;

            case ExplorationEventModifierConfig::REMOVE:
                $this->handleRemoveCase($initialEvent, $modifierConfig);

                break;

            case ExplorationEventModifierConfig::REPLACE:
                $this->handleReplaceCase($initialEvent, $modifierConfig);

                break;
        }

        $events->updateInitialEvent($initialEvent);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }

    private function handleRemoveCase(ExplorationSelectionEvent $event, ExplorationEventModifierConfig $config): void
    {
        /** @var string[] $planetSectors */
        $planetSectors = $event->getPlanetSectorEvents()->getKeys();

        foreach ($planetSectors as $sectorEventName) {
            if ($this->satisfyCriteria($sectorEventName, $config)) {
                $this->removeEvent($event, $sectorEventName);
            }
        }
    }

    private function handleReplaceCase(ExplorationSelectionEvent $event, ExplorationEventModifierConfig $config): void
    {
        /** @var string[] $planetSectors */
        $planetSectors = $event->getPlanetSectorEvents()->getKeys();

        foreach ($planetSectors as $sectorEventName) {
            if ($this->satisfyCriteria($sectorEventName, $config)) {
                $wheight = $this->removeEvent($event, $sectorEventName);
                $this->addEvent($event, $config, $wheight);
            }
        }
    }

    private function addEvent(ExplorationSelectionEvent $event, ExplorationEventModifierConfig $config, ?int $weight = null): void
    {
        $configWeight = $config->getweight();
        if ($configWeight !== null) {
            $weight = $configWeight;
        }
        if ($weight === null) {
            throw new \Exception("Sector event need a weight to be added through modifier {$config->getName()}.");
        }

        $eventToAdd = $config->getEventToAdd();
        if ($eventToAdd === null) {
            throw new \Exception("Modifier {$config->getName()} need an event key to add.");
        }

        $event->getPlanetSectorEvents()->setElementProbability($eventToAdd, $weight);
    }

    // return the weight of the event removed if one is removed
    private function removeEvent(ExplorationSelectionEvent $event, string $planetSectorEventName): ?int
    {
        $planetSectorEvents = $event->getPlanetSectorEvents();

        if ($planetSectorEvents->containsKey($planetSectorEventName)) {
            $eventToRemoveWeight = $planetSectorEvents->getElementProbability($planetSectorEventName);

            $event->setPlanetSectorEvents($planetSectorEvents->withdrawElements([$planetSectorEventName]));

            return $eventToRemoveWeight;
        }

        return null;
    }

    private function satisfyCriteria(string $name, ExplorationEventModifierConfig $config): bool
    {
        switch ($config->getCriteria()) {
            case ExplorationEventModifierConfig::NAME:
                return $name === $config->getEventToRemove();

            case ExplorationEventModifierConfig::EVENT_NAME:
                return $this->findPlanetSectorEventConfigByName($name)->getEventName() === $config->getEventToRemove();
        }

        throw new \Exception("{$config->getCriteria()} is not a valid criteria.");
    }

    private function findPlanetSectorEventConfigByName(string $eventKey): PlanetSectorEventConfig
    {
        $eventConfig = $this->entityManager->getRepository(PlanetSectorEventConfig::class)->findOneByName($eventKey);
        if (!$eventConfig) {
            throw new \RuntimeException('PlanetSectorEventConfig not found for event ' . $eventKey);
        }

        return $eventConfig;
    }
}
