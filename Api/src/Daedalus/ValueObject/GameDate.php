<?php

declare(strict_types=1);

namespace Mush\Daedalus\ValueObject;

use Mush\Daedalus\Entity\Daedalus;

final class GameDate
{
    public function __construct(
        private Daedalus $daedalus,
        private int $day,
        private int $cycle,
    ) {
        if ($day < 1) {
            throw new \InvalidArgumentException('Day must be greater than 0');
        }

        $elapsedCycles = $this->toElapsedCycles();

        $this->day = $this->getDayFromElapsedCycles($elapsedCycles);
        $this->cycle = $this->getCycleFromElapsedCycles($elapsedCycles);
    }

    public function day(): int
    {
        return $this->day;
    }

    public function cycle(): int
    {
        return $this->cycle;
    }

    public function cyclesAgo(int $cycles): self
    {
        return new self($this->daedalus, $this->day, $this->cycle - $cycles);
    }

    public function previous(): self
    {
        if ($this->cycle === 1) {
            return new self($this->daedalus, $this->day - 1, $this->daedalus->getNumberOfCyclesPerDay());
        }

        return new self($this->daedalus, $this->day, $this->cycle - 1);
    }

    public function previousCycle(): int
    {
        return $this->previous()->cycle();
    }

    public function equals(self $otherDate): bool
    {
        return $this->day === $otherDate->day && $this->cycle === $otherDate->cycle;
    }

    public function lessThanOrEqual(self $otherDate): bool
    {
        if ($this->day === $otherDate->day) {
            return $this->cycle <= $otherDate->cycle;
        }

        return $this->day < $otherDate->day;
    }

    public function moreThanOrEqualMinutes(int $minutes): bool
    {
        return $this->toMinutes() >= $minutes;
    }

    private function toElapsedCycles(): int
    {
        return max(1, ($this->day - 1) * $this->daedalus->getNumberOfCyclesPerDay() + $this->cycle);
    }

    private function getDayFromElapsedCycles(int $elapsedCycles): int
    {
        return (int) (($elapsedCycles - 1) / $this->daedalus->getNumberOfCyclesPerDay()) + 1;
    }

    private function getCycleFromElapsedCycles(int $elapsedCycles): int
    {
        return (($elapsedCycles - 1) % $this->daedalus->getNumberOfCyclesPerDay()) + 1;
    }

    private function toMinutes(): int
    {
        return $this->toElapsedCycles() * $this->daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength();
    }
}
