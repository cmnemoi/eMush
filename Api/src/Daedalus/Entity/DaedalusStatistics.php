<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

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

    public function changePlanetsFound(int $delta): static
    {
        $this->planetsFound += $delta;

        return $this;
    }

    public function getExplorationsStarted(): int
    {
        return $this->explorationsStarted;
    }

    public function changeExplorationsStarted(int $delta): static
    {
        $this->explorationsStarted += $delta;

        return $this;
    }

    public function getShipsDestroyed(): int
    {
        return $this->shipsDestroyed;
    }

    public function changeShipsDestroyed(int $delta): static
    {
        $this->shipsDestroyed += $delta;

        return $this;
    }

    public function getRebelBasesContacted(): int
    {
        return $this->rebelBasesContacted;
    }

    public function changeRebelBasesContacted(int $delta): static
    {
        $this->rebelBasesContacted += $delta;

        return $this;
    }

    public function getSporesCreated(): int
    {
        return $this->sporesCreated;
    }

    public function changeSporesCreated(int $delta): static
    {
        $this->sporesCreated += $delta;

        return $this;
    }

    public function getMushAmount(): int
    {
        return $this->mushAmount;
    }

    public function changeMushAmount(int $delta): static
    {
        $this->mushAmount += $delta;

        return $this;
    }
}
