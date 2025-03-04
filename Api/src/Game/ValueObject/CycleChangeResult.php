<?php

namespace Mush\Game\ValueObject;

final readonly class CycleChangeResult
{
    public function __construct(
        public int $daedalusCyclesElapsed,
        public int $explorationCyclesElapsed,
    ) {}

    public function noCycleElapsed(): bool
    {
        return $this->daedalusCyclesElapsed === 0 && $this->explorationCyclesElapsed === 0;
    }

    public function hasDaedalusCycleElapsed(): bool
    {
        return $this->daedalusCyclesElapsed > 0;
    }

    public function hasExplorationCycleElapsed(): bool
    {
        return $this->explorationCyclesElapsed > 0;
    }
}
