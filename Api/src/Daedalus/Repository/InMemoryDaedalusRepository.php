<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Doctrine\DBAL\LockMode;
use Mush\Daedalus\Entity\Daedalus;

final class InMemoryDaedalusRepository implements DaedalusRepositoryInterface
{
    private array $daedalus = [];

    public function clear(): void
    {
        $this->daedalus = [];
    }

    public function findByIdOrThrow(int $id): Daedalus
    {
        foreach ($this->daedalus as $daedalus) {
            if ($daedalus->getId() === $id) {
                return $daedalus;
            }
        }

        throw new \RuntimeException("Daedalus with id {$id} not found");
    }

    public function lockAndRefresh(Daedalus $daedalus, int $mode = LockMode::PESSIMISTIC_WRITE): Daedalus
    {
        // no locking in memory
        return $daedalus;
    }

    public function save(Daedalus $daedalus): void
    {
        $this->daedalus[$daedalus->getName()] = $daedalus;
    }
}
