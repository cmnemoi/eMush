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
    public const FIGHT_8 = 'fight_8';
    public const FIGHT_10 = 'fight_10';
    public const FIGHT_12 = 'fight_12';
    public const FIGHT_15 = 'fight_15';
    public const FIGHT_18 = 'fight_18';
    public const FIGHT_32 = 'fight_32';
    public const FIGHT_8_10_12_15_18_32 = 'fight_8_10_12_15_18_32';
    public const FIND_LOST = 'find_lost';
    public const FUEL = 'fuel';
    public const FUEL_1 = 'fuel_1';
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
    public const PROVISION_1 = 'provision_1';
    public const PROVISION_2 = 'provision_2';
    public const PROVISION_3 = 'provision_3';
    public const PROVISION_4 = 'provision_4';
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
        $this->addTag(self::PLANET_SECTOR_EVENT);
        $this->addTag($this->config->getEventName());
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

    public function getOutputTable(): ProbaCollection
    {
        return $this->config->getOutputTable();
    }

    public function getOutputQuantity(): ProbaCollection
    {
        return $this->config->getOutputQuantity();
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

        if ($this->config->getOutputTable()->isEmpty()) {
            return $logParameters;
        }

        $minQuantity = $this->config->getOutputTable()->minElement();
        $maxQuantity = $this->config->getOutputTable()->maxElement();

        $logParameters['min_quantity'] = $minQuantity;
        $logParameters['max_quantity'] = $maxQuantity;

        return $logParameters;
    }
}
