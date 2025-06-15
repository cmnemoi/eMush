<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\FunFactEnum;

#[ORM\Embeddable]
class PlayerStatistics
{
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $timesCooked = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $planetScanRatio = 0;
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
    private int $commsAdvanced = 0;
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
    private int $aggressiveActionsDone = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $kubeUsed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $traitorUsed = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $stealthActionsTaken = 0;
    #[ORM\Column(type: 'integer', nullable: false, options : ['default' => 0])]
    private int $unstealthActionsTaken = 0;

    public function getTimesCooked(): int
    {
        return $this->timesCooked;
    }

    public function incrementTimesCooked(): static
    {
        ++$this->timesCooked;

        return $this;
    }

    public function getPlanetScanRatio(): int
    {
        return $this->planetScanRatio;
    }

    public function changePlanetScanRatio(int $delta): static
    {
        $this->planetScanRatio += $delta;

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

    public function getTechFailRate(): float
    {
        return $this->getTechFails() > 0 ? $this->getTechFails() / ($this->getTechFails() + $this->getTechSuccesses()) : 0;
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

    public function getCommsAdvanced(): int
    {
        return $this->commsAdvanced;
    }

    public function incrementCommsAdvanced(): static
    {
        ++$this->commsAdvanced;

        return $this;
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

    public function getMutateDamageDealt(): int
    {
        return $this->mutantDamageDealt;
    }

    public function incrementMutateDamageDealt(int $delta): static
    {
        if ($delta < 0) {
            throw new \LogicException("Increase for mutate damage dealt statistic shouldn't be negative");
        }

        $this->mutantDamageDealt += $delta;

        return $this;
    }

    public function getHuntersDestroyed(): int
    {
        return $this->huntersDestroyed;
    }

    public function incrementHuntersDestroyed(): static
    {
        ++$this->huntersDestroyed;

        return $this;
    }

    public function getLostCycles(): int
    {
        return $this->lostCycles;
    }

    public function incrementLostCycles(): static
    {
        ++$this->lostCycles;

        return $this;
    }

    public function getKillCount(): int
    {
        return $this->timesKilled;
    }

    public function incrementKillCount(): static
    {
        ++$this->timesKilled;

        return $this;
    }

    public function getMessageCount(): int
    {
        return $this->timesTalked;
    }

    public function incrementMessageCount(): static
    {
        ++$this->timesTalked;

        return $this;
    }

    public function getInjuryCount(): int
    {
        return $this->injuriesContracted;
    }

    public function incrementInjuryCount(): static
    {
        ++$this->injuriesContracted;

        return $this;
    }

    public function getIllnessCount(): int
    {
        return $this->illnessesContracted;
    }

    public function incrementIllnessCount(): static
    {
        ++$this->illnessesContracted;

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

    public function getKnifeDodged(): int
    {
        return $this->knifeDodged;
    }

    public function incrementKnifeDodged(): static
    {
        ++$this->knifeDodged;

        return $this;
    }

    public function getAggressiveActionsDone(): int
    {
        return $this->aggressiveActionsDone;
    }

    public function incrementAggressiveActionsCount(): static
    {
        ++$this->aggressiveActionsDone;

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

    public function getTraitorUsed(): int
    {
        return $this->traitorUsed;
    }

    public function incrementTraitorUsed(): static
    {
        ++$this->traitorUsed;

        return $this;
    }

    public function getStealthActionsTaken(): int
    {
        return $this->stealthActionsTaken;
    }

    public function incrementStealthActionsTaken(): static
    {
        ++$this->stealthActionsTaken;

        return $this;
    }

    public function getUnstealthActionsTaken(): int
    {
        return $this->unstealthActionsTaken;
    }

    public function incrementUnstealthActionsTaken(): static
    {
        ++$this->unstealthActionsTaken;

        return $this;
    }

    public function getNumberStatisticForFunFact(string $funFact): int
    {
        return match ($funFact) {
            FunFactEnum::BEST_COOK => $this->getTimesCooked(),
            FunFactEnum::BEST_PLANET_SCANNER => $this->getPlanetScanRatio(),
            FunFactEnum::BEST_TECHNICIAN => $this->getTechSuccesses(),
            FunFactEnum::SOL_COLLABS => $this->getLinkImproved(),
            FunFactEnum::BEST_CARESSER => $this->getTimesCaressed(),
            FunFactEnum::BEST_HUNTER_KILLER => $this->getHuntersDestroyed(),
            FunFactEnum::BEST_LOST => $this->getLostCycles(),
            FunFactEnum::BEST_KILLER => $this->getKillCount(),
            FunFactEnum::MOST_TALKATIVE, FunFactEnum::LESS_TALKATIVE => $this->getMessageCount(),
            FunFactEnum::LESS_ACTIVE, FunFactEnum::MOST_ACTIVE => $this->getActionPointsUsed(),
            FunFactEnum::BEST_EATER => $this->getTimesEaten(),
            FunFactEnum::BEST_ACTION_WASTER, FunFactEnum::WORST_ACTION_WASTER => $this->getActionPointsWasted(),
            FunFactEnum::BEST_SLEEPER => $this->getSleptCycles(),
            FunFactEnum::BEST_HACKER => $this->getTimesHacked(),
            FunFactEnum::BEST_COM_TECHNICIAN => $this->getCommsAdvanced(),
            FunFactEnum::BEST_SANDMAN => $this->getSleepInterupted(),
            FunFactEnum::BEST_TERRORIST => $this->getMutateDamageDealt(),
            FunFactEnum::BEST_WOUNDED => $this->getInjuryCount(),
            FunFactEnum::BEST_DISEASED => $this->getIllnessCount(),
            FunFactEnum::DRUG_ADDICT, FunFactEnum::LESSER_DRUGGED => $this->getDrugsTaken(),
            FunFactEnum::KNIFE_EVADER => $this->getKnifeDodged(),
            FunFactEnum::BEST_AGRO, FunFactEnum::WORST_AGRO => $this->getAggressiveActionsDone(),
            FunFactEnum::KUBE_ADDICT => $this->getKubeUsed(),
            FunFactEnum::BEST_ALIEN_TRAITOR => $this->getTraitorUsed(),
            FunFactEnum::STEALTHIEST => $this->getStealthActionsTaken(),
            default => throw new \LogicException('Cannot get numeric value for this fun fact'),
        };
    }
}
