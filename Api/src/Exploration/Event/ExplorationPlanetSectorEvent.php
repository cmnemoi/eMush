<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Exploration\Entity\ExplorationPlanetSectorEventConfig;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\RoomLog\Event\LoggableEventInterface;

/** @codeCoverageIgnore */
class ExplorationPlanetSectorEvent extends ExplorationEvent implements LoggableEventInterface
{
    public const ACCIDENT = 'accident';
    public const AGAIN = 'again';
    public const ARTEFACT = 'artefact';
    public const BACK = 'back';
    public const DISASTER = 'disaster';
    public const DISEASE = 'disease';
    public const FIGHT = 'fight';
    public const FIND_LOST = 'find_lost';
    public const FUEL = 'fuel';
    public const HARVEST = 'harvest';
    public const ITEM_LOST = 'item_lost';
    public const KILL_ALL = 'kill_all';
    public const KILL_LOST = 'kill_lost';
    public const KILL_RANDOM = 'kill_random';
    public const MUSH_TRAP = 'mush_trap';
    public const PLAYER_LOST = 'player_lost';
    public const NOTHING_TO_REPORT = 'nothing_to_report';
    public const OXYGEN = 'oxygen';
    public const PROVISION = 'provision';
    public const STARMAP = 'starmap';
    public const TIRED = 'tired';

    private PlayerCollection $explorators;
    private Place $place;
    private string $visibility;
    private PlanetSector $planetSector;
    private ExplorationPlanetSectorEventConfig $config;

    public function __construct(
        PlanetSector $planetSector,
        ExplorationPlanetSectorEventConfig $config,
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
    }

    public function getPlanetSector(): PlanetSector
    {
        return $this->planetSector;
    }

    public function getConfig(): ExplorationPlanetSectorEventConfig
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
        $logParameters = [];

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
