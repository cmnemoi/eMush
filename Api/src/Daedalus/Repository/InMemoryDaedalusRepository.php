<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\Daedalus;

final class InMemoryDaedalusRepository implements DaedalusRepositoryInterface
{
    private array $daedalus = [];

    public function clear(): void
    {
        $this->daedalus = [];
    }

    public function save(Daedalus $daedalus): void
    {
        $this->daedalus[$daedalus->getName()] = $daedalus;
    }
}
