<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\Neron;

final class InMemoryNeronRepository implements NeronRepositoryInterface
{
    private array $nerons = [];

    public function save(Neron $neron): void
    {
        $this->nerons[] = $neron;
    }

    public function clear(): void
    {
        $this->nerons = [];
    }
}
