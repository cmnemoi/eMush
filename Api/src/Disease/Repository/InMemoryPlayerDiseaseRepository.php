<?php

declare(strict_types=1);

namespace Mush\Disease\Repository;

use Mush\Disease\Entity\PlayerDisease;

final class InMemoryPlayerDiseaseRepository implements PlayerDiseaseRepositoryInterface
{
    private array $diseases = [];

    public function save(PlayerDisease $playerDisease): void
    {
        if (!$playerDisease->getId()) {
            $this->setDiseaseId($playerDisease);
        }

        $this->diseases[$playerDisease->getIdOrThrow()] = $playerDisease;
    }

    public function delete(PlayerDisease $playerDisease): void
    {
        $playerDisease->getPlayer()->removeMedicalCondition($playerDisease);
        unset($this->diseases[$playerDisease->getIdOrThrow()]);
    }

    public function clear(): void
    {
        $this->diseases = [];
    }

    public function findByIdOrThrow(int $id): PlayerDisease
    {
        return $this->diseases[$id] ?? throw new \RuntimeException("PlayerDisease with id {$id} not found");
    }

    public function findByIdOrNull(int $id): ?PlayerDisease
    {
        return $this->diseases[$id] ?? null;
    }

    public function count(): int
    {
        return \count($this->diseases);
    }

    private function setDiseaseId(PlayerDisease $playerDisease): void
    {
        (new \ReflectionProperty($playerDisease, 'id'))->setValue($playerDisease, crc32(serialize($playerDisease)));
    }
}
