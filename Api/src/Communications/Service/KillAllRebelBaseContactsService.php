<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Repository\RebelBaseRepositoryInterface;

final readonly class KillAllRebelBaseContactsService
{
    public function __construct(
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {}

    public function execute(int $daedalusId): void
    {
        $rebelBases = $this->rebelBaseRepository->findAllContactingRebelBases($daedalusId);

        foreach ($rebelBases as $rebelBase) {
            $rebelBase->endContact();
            $this->rebelBaseRepository->save($rebelBase);
        }
    }
}
