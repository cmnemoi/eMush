<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;

final class InMemoryDaedalusRepository implements DaedalusRepositoryInterface
{
    private array $daedalus = [];

    public function clear(): void
    {
        $this->daedalus = [];
    }

    public function findByName(string $name): Daedalus
    {
        return $this->daedalus[$name];
    }

    public function save(Daedalus $daedalus): void
    {
        $this->daedalus[$daedalus->getName()] = $daedalus;
    }
}
