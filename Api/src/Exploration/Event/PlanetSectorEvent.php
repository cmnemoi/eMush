<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\RoomLog\Event\LoggableEventInterface;

/** @codeCoverageIgnore */
class PlanetSectorEvent extends ExplorationEvent implements LoggableEventInterface
{
    public const ACCIDENT = 'accident';
    public const ACCIDENT_3_5 = 'accident_3_5';
    public const AGAIN = 'again';
    public const ARTEFACT = 'artefact';
    public const BACK = 'back';
    public const DISASTER = 'disaster';
    public const DISASTER_3_5 = 'disaster_3_5';
    public const DISEASE = 'disease';
    public const FIGHT = 'fight';
    public const FIND_LOST = 'find_lost';
    public const FUEL = 'fuel';
    public const FUEL_2 = 'fuel_2';
    public const FUEL_3 = 'fuel_3';
    public const FUEL_4 = 'fuel_4';
    public const FUEL_5 = 'fuel_5';
    public const FUEL_6 = 'fuel_6';
    public const HARVEST = 'harvest';
    public const ITEM_LOST = 'item_lost';
    public const KILL_ALL = 'kill_all';
    public const KILL_LOST = 'kill_lost';
    public const KILL_RANDOM = 'kill_random';
    public const MUSH_TRAP = 'mush_trap';
    public const PLAYER_LOST = 'player_lost';
    public const NOTHING_TO_REPORT = 'nothing_to_report';
    public const OXYGEN = 'oxygen';
    public const OXYGEN_8 = 'oxygen_8';
    public const OXYGEN_16 = 'oxygen_16';
    public const OXYGEN_24 = 'oxygen_24';
    public const PROVISION = 'provision';
    public const STARMAP = 'starmap';
    public const TIRED = 'tired';
    public const TIRED_2 = 'tired_2';
    public const PLANET_SECTOR_EVENT = 'planet_sector_event';

    private PlayerCollection $explorators;
    private Place $place;
    private string $visibility;
    private PlanetSector $planetSector;
    private PlanetSectorEventConfig $config;

    public function __construct(
        PlanetSector $planetSector,
        PlanetSectorEventConfig $config,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::PUBLIC,
    ) {
        $exploration = $planetSector->getPlanet()->getExploration();
        if ($exploration === null) {
            throw new \RuntimeException('You need an exploration to create an exploration event');
        }

        parent::__construct($exploration, $tags, $time);
        $this->planetSector = $planetSector;
        $this->config = $config;
        $this->explorators = $this->exploration->getExplorators();
        $this->place = $this->exploration->getDaedalus()->getPlanetPlace();
        $this->visibility = $visibility;
        $this->addTag($this->config->getEventName());
    }

    public function getName(): string
    {
        return $this->config->getEventName();
    }

    public function getName(): string
    {
        return $this->config->getEventName();
    }

    public function getPlanetSector(): PlanetSector
    {
        return $this->planetSector;
    }

    public function getConfig(): PlanetSectorEventConfig
    {
        return $this->config;
    }

    public function getOutputQuantityTable(): ?ProbaCollection
    {
        return $this->config->getOutputQuantityTable();
    }

    public function getExplorators(): PlayerCollection
    {
        return $this->explorators;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [
            'equipment' => $this->exploration->getShipUsedName(),
        ];

        $minQuantity = $this->config->getOutputQuantityTable()?->minElement();
        $maxQuantity = $this->config->getOutputQuantityTable()?->maxElement();

        if ($minQuantity) {
            $logParameters['min_quantity'] = $minQuantity;
        }
        if ($maxQuantity) {
            $logParameters['max_quantity'] = $maxQuantity;
        }

        return $logParameters;
    }
}
