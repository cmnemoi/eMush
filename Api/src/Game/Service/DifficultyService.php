<?php

declare(strict_types=1);

namespace Mush\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Enum\DifficultyEnum;

final class DifficultyService implements DifficultyServiceInterface
{
    public function __construct(private DaedalusRepositoryInterface $daedalusRepository) {}

    public function updateDaedalusDifficulty(Daedalus $daedalus, bool $skipIncidentUpdate = false): void
    {
        $skipIncidentUpdate ? null : $this->updateIncidentPoints($daedalus);
        $this->updateHunterPoints($daedalus);
    }

    private function updateHunterPoints(Daedalus $daedalus): void
    {
        $pointsToAdd = $daedalus->getDay() + 6;
        if ($daedalus->isInHardMode()) {
            ++$pointsToAdd;
        }
        if ($daedalus->isInVeryHardMode()) {
            $pointsToAdd += 2;
        }
        $pointsToAdd = (int) round($pointsToAdd * $this->getActivityOverload($daedalus));

        $daedalus->addHunterPoints($pointsToAdd);
        $this->daedalusRepository->save($daedalus);
    }

    private function updateIncidentPoints(Daedalus $daedalus): void
    {
        if ($daedalus->isFilling()) {
            return;
        }

        $pointsToAdd = $daedalus->getDay() <= 2 ? $daedalus->getDay() : 1;

        if ($daedalus->isInHardMode()) {
            $pointsToAdd += $this->getHardModeOverload($daedalus);
        }
        if ($daedalus->isInVeryHardMode()) {
            $pointsToAdd += $this->getVeryHardModeOverload($daedalus);
        }

        $pointsToAdd = (int) round($pointsToAdd * $this->getActivityOverload($daedalus));

        $daedalus->addIncidentPoints($pointsToAdd);
        $this->daedalusRepository->save($daedalus);
    }

    /**
     * This function adds extra difficulty points if players spend too much action points
     * (dynamic difficulty).
     */
    private function getActivityOverload(Daedalus $daedalus): float
    {
        $threshold = 7 * $daedalus->getAlivePlayers()->count();
        if ($threshold < 1) {
            return 1;
        }
        if ($daedalus->getDailyActionPointsSpent() <= $threshold) {
            return 1;
        }

        return $daedalus->getDailyActionPointsSpent() / $threshold;
    }

    private function getHardModeOverload(Daedalus $daedalus): float
    {
        return 1 + $daedalus->getDay() - $daedalus->getGameConfig()->getDifficultyConfig()->getDifficultyModes()->get(DifficultyEnum::HARD);
    }

    private function getVeryHardModeOverload(Daedalus $daedalus): float
    {
        return 2 + $this->getHardModeOverload($daedalus) + $daedalus->getDay() - $daedalus->getGameConfig()->getDifficultyConfig()->getDifficultyModes()->get(DifficultyEnum::VERY_HARD);
    }
}
