<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\ValueObject\NamedInteger;

#[ORM\Embeddable]
class DaedalusStatistics
{
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $planetsFound = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $explorationsStarted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $shipsDestroyed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $rebelBasesContacted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $sporesCreated = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $mushAmount = 0;

    public function __construct(int $planetsFound = 0, int $explorationsStarted = 0, int $shipsDestroyed = 0, int $rebelBasesContacted = 0, int $sporesCreated = 0, int $mushAmount = 0)
    {
        $this->planetsFound = $planetsFound;
        $this->explorationsStarted = $explorationsStarted;
        $this->shipsDestroyed = $shipsDestroyed;
        $this->rebelBasesContacted = $rebelBasesContacted;
        $this->sporesCreated = $sporesCreated;
        $this->mushAmount = $mushAmount;
    }

    public function getPlanetsFound(): int
    {
        return $this->planetsFound;
    }

    public function incrementPlanetsFound(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->planetsFound += $delta;

        return $this;
    }

    public function getExplorationsStarted(): int
    {
        return $this->explorationsStarted;
    }

    public function incrementExplorationsStarted(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->explorationsStarted += $delta;

        return $this;
    }

    public function getShipsDestroyed(): int
    {
        return $this->shipsDestroyed;
    }

    public function incrementShipsDestroyed(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->shipsDestroyed += $delta;

        return $this;
    }

    public function getRebelBasesContacted(): int
    {
        return $this->rebelBasesContacted;
    }

    public function incrementRebelBasesContacted(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->rebelBasesContacted += $delta;

        return $this;
    }

    public function getSporesCreated(): int
    {
        return $this->sporesCreated;
    }

    public function incrementSporesCreated(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->sporesCreated += $delta;

        return $this;
    }

    public function getMushAmount(): int
    {
        return $this->mushAmount;
    }

    public function incrementMushAmount(int $delta = 1): static
    {
        if ($delta < 0) {
            return $this;
        }

        $this->mushAmount += $delta;

        return $this;
    }

    /**
     * @return array<string, NamedInteger>
     */
    public function toArray(): array
    {
        return [
            'planetsFound' => new NamedInteger('planetsFound', $this->planetsFound),
            'explorationsStarted' => new NamedInteger('explorationsStarted', $this->explorationsStarted),
            'shipsDestroyed' => new NamedInteger('shipsDestroyed', $this->shipsDestroyed),
            'sporesCreated' => new NamedInteger('sporesCreated', $this->sporesCreated),
            'mushAmount' => new NamedInteger('mushAmount', $this->mushAmount),
            'rebelBasesContacted' => new NamedInteger('rebelBasesContacted', $this->rebelBasesContacted),
        ];
    }
}
