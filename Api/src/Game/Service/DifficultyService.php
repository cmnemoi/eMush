<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;

class DifficultyService implements DifficultyServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function updateDaedalusDifficultyPoints(Daedalus $daedalus, string $pointsType): void
    {
        switch ($pointsType) {
            case DaedalusVariableEnum::HUNTER_POINTS:
                $this->updateHunterPoints($daedalus);

                break;
        }
    }

    /**
     * This function adds extra difficulty points if players spend too much action points
     * (dynamic difficulty).
     */
    private function getExtraPoints(Daedalus $daedalus): float
    {
        $threshold = 7 * $daedalus->getPlayers()->getPlayerAlive()->count();
        if ($threshold < 1) {
            return 1;
        }
        if ($daedalus->getDailyActionPointsSpent() <= $threshold) {
            return 1;
        }

        return $daedalus->getDailyActionPointsSpent() / $threshold;
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
        $pointsToAdd = (int) ($pointsToAdd * $this->getExtraPoints($daedalus) + 0.5);

        $daedalus->addHunterPoints($pointsToAdd);
        $this->persist([$daedalus]);
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
