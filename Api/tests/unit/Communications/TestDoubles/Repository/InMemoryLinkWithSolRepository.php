<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;

final class InMemoryLinkWithSolRepository implements LinkWithSolRepositoryInterface
{
    private array $linkWithSols = [];

    public function deleteByDaedalusId(int $daedalusId): void
    {
        unset($this->linkWithSols[$daedalusId]);
    }

    public function findByDaedalusIdOrThrow(int $daedalusId): LinkWithSol
    {
        return $this->linkWithSols[$daedalusId] ?? throw new \RuntimeException("LinkWithSol not found for daedalus id {$daedalusId}");
    }

    public function save(LinkWithSol $linkWithSol): void
    {
        $this->linkWithSols[$linkWithSol->getDaedalusId()] = $linkWithSol;
    }
}
