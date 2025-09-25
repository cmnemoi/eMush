<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus\TestDoubles;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Repository\ClosedDaedalusRepositoryInterface;

final class InMemoryClosedDaedalusRepository implements ClosedDaedalusRepositoryInterface
{
    private array $closedDaedalus = [];

    public function __construct(array $closedDaedalus = [])
    {
        $this->closedDaedalus = $closedDaedalus;
    }

    public function save(ClosedDaedalus $closedDaedalus): void
    {
        $this->closedDaedalus[$closedDaedalus->getId()] = $closedDaedalus;
    }

    public function findOneByIdOrThrow(int $id): ClosedDaedalus
    {
        if (!isset($this->closedDaedalus[$id])) {
            throw new \RuntimeException("ClosedDaedalus {$id} not found");
        }

        return $this->closedDaedalus[$id];
    }
}
