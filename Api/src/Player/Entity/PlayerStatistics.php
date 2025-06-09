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
    private int $actionsDone = 0;
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

    public function getActionsDone(): int
    {
        return $this->actionsDone;
    }

    public function incrementActionsDone(): static
    {
        ++$this->actionsDone;

        return $this;
    }

    public function getActionPointsUsed(): int
    {
        return $this->actionPointsUsed;
    }

    public function incrementActionPointsUsed(int $delta): static
    {
        if ($delta < 0) {
            throw new \LogicException("Delta for action points used shouldn't be negative");
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
            throw new \LogicException("Delta for action points used shouldn't be negative");
        }

        $this->actionPointsWasted += $delta;

        return $this;
    }
}
