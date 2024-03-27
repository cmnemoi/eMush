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
    public const string ACCIDENT = 'accident';
    public const string ACCIDENT_3_5 = 'accident_3_5';
    public const string AGAIN = 'again';
    public const string ARTEFACT = 'artefact';
    public const string BACK = 'back';
    public const string DISASTER = 'disaster';
    public const string DISASTER_3_5 = 'disaster_3_5';
    public const string DISEASE = 'disease';
    public const string FIGHT = 'fight';
    public const string FIGHT_8 = 'fight_8';
    public const string FIGHT_10 = 'fight_10';
    public const string FIGHT_12 = 'fight_12';
    public const string FIGHT_15 = 'fight_15';
    public const string FIGHT_18 = 'fight_18';
    public const string FIGHT_32 = 'fight_32';
    public const string FIGHT_8_10_12_15_18_32 = 'fight_8_10_12_15_18_32';
    public const string FIND_LOST = 'find_lost';
    public const string FUEL = 'fuel';
    public const string FUEL_1 = 'fuel_1';
    public const string FUEL_2 = 'fuel_2';
    public const string FUEL_3 = 'fuel_3';
    public const string FUEL_4 = 'fuel_4';
    public const string FUEL_5 = 'fuel_5';
    public const string FUEL_6 = 'fuel_6';
    public const string HARVEST = 'harvest';
    public const string HARVEST_1 = 'harvest_1';
    public const string HARVEST_2 = 'harvest_2';
    public const string HARVEST_3 = 'harvest_3';
    public const string ITEM_LOST = 'item_lost';
    public const string KILL_ALL = 'kill_all';
    public const string KILL_LOST = 'kill_lost';
    public const string KILL_RANDOM = 'kill_random';
    public const string MUSH_TRAP = 'mush_trap';
    public const string PLAYER_LOST = 'player_lost';
    public const string NOTHING_TO_REPORT = 'nothing_to_report';
    public const string OXYGEN = 'oxygen';
    public const string OXYGEN_8 = 'oxygen_8';
    public const string OXYGEN_16 = 'oxygen_16';
    public const string OXYGEN_24 = 'oxygen_24';
    public const string PROVISION = 'provision';
    public const string PROVISION_1 = 'provision_1';
    public const string PROVISION_2 = 'provision_2';
    public const string PROVISION_3 = 'provision_3';
    public const string PROVISION_4 = 'provision_4';
    public const string STARMAP = 'starmap';
    public const string TIRED = 'tired';
    public const string TIRED_2 = 'tired_2';
    public const string PLANET_SECTOR_EVENT = 'planet_sector_event';

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
        $this->addTag($this->planetSector->getName());
    }

    public function getKey(): string
    {
        return $this->config->getName();
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
