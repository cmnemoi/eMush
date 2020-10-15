<?php


namespace Mush\Daedalus\Entity;


class DaedalusConfig
{
    private int $initOxygen;

    private int $initFuel;

    private int $initHull;

    private int $initShield;

    private array $randomItemPlace;

    private array $rooms;

    public function getInitOxygen(): int
    {
        return $this->initOxygen;
    }

    public function setInitOxygen(int $initOxygen): DaedalusConfig
    {
        $this->initOxygen = $initOxygen;
        return $this;
    }

    public function getInitFuel(): int
    {
        return $this->initFuel;
    }

    public function setInitFuel(int $initFuel): DaedalusConfig
    {
        $this->initFuel = $initFuel;
        return $this;
    }

    public function getInitHull(): int
    {
        return $this->initHull;
    }

    public function setInitHull(int $initHull): DaedalusConfig
    {
        $this->initHull = $initHull;
        return $this;
    }

    public function getInitShield(): int
    {
        return $this->initShield;
    }

    public function setInitShield(int $initShield): DaedalusConfig
    {
        $this->initShield = $initShield;
        return $this;
    }

    public function getRandomItemPlace(): array
    {
        return $this->randomItemPlace;
    }

    public function setRandomItemPlace(array $randomItemPlace): DaedalusConfig
    {
        $this->randomItemPlace = $randomItemPlace;
        return $this;
    }

    public function getRooms(): array
    {
        return $this->rooms;
    }

    public function setRooms(array $rooms): DaedalusConfig
    {
        $this->rooms = $rooms;
        return $this;
    }
}