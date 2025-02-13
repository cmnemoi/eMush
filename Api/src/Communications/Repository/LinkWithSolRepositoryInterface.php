<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\LinkWithSol;

interface LinkWithSolRepositoryInterface
{
    public function deleteByDaedalusId(int $daedalusId): void;

    public function findByDaedalusIdOrThrow(int $daedalusId): LinkWithSol;

    public function save(LinkWithSol $linkWithSol): void;
}
