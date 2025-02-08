<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Repository\LinkWithSolRepositoryInterface;

final readonly class EstablishLinkWithSolService
{
    public function __construct(private LinkWithSolRepositoryInterface $linkWithSolRepository) {}

    public function execute(int $daedalusId, int $strengthIncrease): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow(daedalusId: $daedalusId);

        $linkWithSol->increaseStrength($strengthIncrease);
        $linkWithSol->markAsEstablished();

        $this->linkWithSolRepository->save($linkWithSol);
    }
}
