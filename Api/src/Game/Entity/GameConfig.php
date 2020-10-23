<?php

namespace Mush\Game\Entity;

class GameConfig
{
    private int $maxPlayer;
    private int $nbMush;
    private int $cycleLength;
    private string $timeZone;
    private string $language;
    private int $initHealthPoint;
    private int $maxHealthPoint;
    private int $initMoralPoint;
    private int $maxMoralPoint;
    private int $initSatiety;
    private int $initActionPoint;
    private int $maxActionPoint;
    private int $initMovementPoint;
    private int $maxMovementPoint;
    private int $maxItemInInventory;

    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }

    public function setMaxPlayer(int $maxPlayer): GameConfig
    {
        $this->maxPlayer = $maxPlayer;
        return $this;
    }

    public function getNbMush(): int
    {
        return $this->nbMush;
    }

    public function setNbMush(int $nbMush): GameConfig
    {
        $this->nbMush = $nbMush;
        return $this;
    }

    public function getCycleLength(): int
    {
        return $this->cycleLength;
    }

    public function getNumberOfCyclePerDay(): int
    {
        return 24 / $this->getCycleLength();
    }

    public function setCycleLength(int $cycleLength): GameConfig
    {
        $this->cycleLength = $cycleLength;
        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): GameConfig
    {
        $this->timeZone = $timeZone;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): GameConfig
    {
        $this->language = $language;
        return $this;
    }

    public function getInitHealthPoint(): int
    {
        return $this->initHealthPoint;
    }

    public function setInitHealthPoint(int $initHealthPoint): GameConfig
    {
        $this->initHealthPoint = $initHealthPoint;
        return $this;
    }

    public function getMaxHealthPoint(): int
    {
        return $this->maxHealthPoint;
    }

    public function setMaxHealthPoint(int $maxHealthPoint): GameConfig
    {
        $this->maxHealthPoint = $maxHealthPoint;
        return $this;
    }

    public function getInitMoralPoint(): int
    {
        return $this->initMoralPoint;
    }

    public function setInitMoralPoint(int $initMoralPoint): GameConfig
    {
        $this->initMoralPoint = $initMoralPoint;
        return $this;
    }

    public function getMaxMoralPoint(): int
    {
        return $this->maxMoralPoint;
    }

    public function setMaxMoralPoint(int $maxMoralPoint): GameConfig
    {
        $this->maxMoralPoint = $maxMoralPoint;
        return $this;
    }

    public function getInitSatiety(): int
    {
        return $this->initSatiety;
    }

    public function setInitSatiety(int $initSatiety): GameConfig
    {
        $this->initSatiety = $initSatiety;
        return $this;
    }

    public function getInitActionPoint(): int
    {
        return $this->initActionPoint;
    }

    public function setInitActionPoint(int $initActionPoint): GameConfig
    {
        $this->initActionPoint = $initActionPoint;
        return $this;
    }

    public function getMaxActionPoint(): int
    {
        return $this->maxActionPoint;
    }

    public function setMaxActionPoint(int $maxActionPoint): GameConfig
    {
        $this->maxActionPoint = $maxActionPoint;
        return $this;
    }

    public function getInitMovementPoint(): int
    {
        return $this->initMovementPoint;
    }

    public function setInitMovementPoint(int $initMovementPoint): GameConfig
    {
        $this->initMovementPoint = $initMovementPoint;
        return $this;
    }

    public function getMaxMovementPoint(): int
    {
        return $this->maxMovementPoint;
    }

    public function setMaxMovementPoint(int $maxMovementPoint): GameConfig
    {
        $this->maxMovementPoint = $maxMovementPoint;
        return $this;
    }

    public function getMaxItemInInventory(): int
    {
        return $this->maxItemInInventory;
    }

    public function setMaxItemInInventory(int $maxItemInInventory): GameConfig
    {
        $this->maxItemInInventory = $maxItemInInventory;
        return $this;
    }
}
