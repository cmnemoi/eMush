<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class PlayerStatistics
{
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesCooked = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $planetsFullyScanned = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $techSuccesses = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $techFails = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $linkImproved = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesCaressed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $huntersDestroyed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $lostCycles = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesKilled = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesTalked = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $actionPointsUsed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $actionPointsWasted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesEaten = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $sleptCycles = 0;
    #[ORM\Column(type: 'boolean', nullable: false, options : ['default' => false])]
    private bool $diedDuringSleep = false;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesHacked = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $linkFixed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $sleepInterupted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $mutantDamageDealt = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $injuriesContracted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $illnessesContracted = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $drugsTaken = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $knifeDodged = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $attackedTimes = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $kubeUsed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $traitorUsed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $uncoveredSecretActionsTaken = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $revealedSecretActionsTaken = 0;

    public function getTimesCooked(): int
    {
        return $this->timesCooked;
    }

    public function incrementTimesCooked(): static
    {
        ++$this->timesCooked;

        return $this;
    }

    public function getTechSuccesses(): int
    {
        return $this->techSuccesses;
    }

    public function incrementTechSuccesses(): static
    {
        ++$this->techSuccesses;

        return $this;
    }

    public function getTechFails(): int
    {
        return $this->techFails;
    }

    public function incrementTechFails(): static
    {
        ++$this->techFails;

        return $this;
    }

    public function getLinkImproved(): int
    {
        return $this->linkImproved;
    }

    public function incrementLinkImproved(): static
    {
        ++$this->linkImproved;

        return $this;
    }

    public function getTimesCaressed(): int
    {
        return $this->timesCaressed;
    }

    public function incrementTimesCaressed(): static
    {
        ++$this->timesCaressed;

        return $this;
    }

    public function getActionPointsUsed(): int
    {
        return $this->actionPointsUsed;
    }

    public function incrementActionPointsUsed(int $delta): static
    {
        if ($delta < 0) {
            throw new \LogicException("Increase for action points used statistic shouldn't be negative");
        }

        $this->actionPointsUsed += $delta;

        return $this;
    }

    public function getActionPointsWasted(): int
    {
        return $this->actionPointsWasted;
    }

    public function incrementActionPointsWasted(int $delta): static
    {
        if ($delta < 0) {
            throw new \LogicException("Increase for action points wasted statistic shouldn't be negative");
        }

        $this->actionPointsWasted += $delta;

        return $this;
    }

    public function getTimesEaten(): int
    {
        return $this->timesEaten;
    }

    public function incrementTimesEaten(): static
    {
        ++$this->timesEaten;

        return $this;
    }

    public function getSleptCycles(): int
    {
        return $this->sleptCycles;
    }

    public function incrementSleptByCycle(): static
    {
        ++$this->sleptCycles;

        return $this;
    }

    public function hasDiedDuringSleep(): bool
    {
        return $this->diedDuringSleep;
    }

    public function markAsDiedDuringSleep(): static
    {
        $this->diedDuringSleep = true;

        return $this;
    }

    public function getTimesHacked(): int
    {
        return $this->timesHacked;
    }

    public function incrementTimesHacked(): static
    {
        ++$this->timesHacked;

        return $this;
    }

    public function getLinkFixed(): int
    {
        return $this->linkFixed;
    }

    public function incrementLinkFixed(): static
    {
        ++$this->linkFixed;

        return $this->incrementLinkImproved();
    }

    public function getSleepInterupted(): int
    {
        return $this->sleepInterupted;
    }

    public function incrementSleepInterupted(): static
    {
        ++$this->sleepInterupted;

        return $this;
    }

    public function getDrugsTaken(): int
    {
        return $this->drugsTaken;
    }

    public function incrementDrugsTaken(): static
    {
        ++$this->drugsTaken;

        return $this;
    }

    public function getKubeUsed(): int
    {
        return $this->kubeUsed;
    }

    public function incrementKubeUsed(): static
    {
        ++$this->kubeUsed;

        return $this;
    }
}
