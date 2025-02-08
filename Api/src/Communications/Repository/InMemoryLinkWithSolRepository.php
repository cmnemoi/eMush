<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\LinkWithSol;

final class InMemoryLinkWithSolRepository implements LinkWithSolRepositoryInterface
{
    private array $linkWithSols = [];

    public function findByDaedalusIdOrThrow(int $daedalusId): LinkWithSol
    {
        return $this->linkWithSols[$daedalusId] ?? throw new \RuntimeException("LinkWithSol not found for daedalus id {$daedalusId}");
    }

    public function save(LinkWithSol $linkWithSol): void
    {
        $this->linkWithSols[$linkWithSol->getDaedalusId()] = $linkWithSol;
    }
}
