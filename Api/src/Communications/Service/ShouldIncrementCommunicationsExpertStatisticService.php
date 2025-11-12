<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Status\Repository\StatusRepositoryInterface;

final readonly class ShouldIncrementCommunicationsExpertStatisticService
{
    public function __construct(
        private NeronVersionRepositoryInterface $neronVersion,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private StatusRepositoryInterface $statusRepository,
        private XylophRepositoryInterface $xylophEntryRepository,
    ) {}

    public function execute(int $daedalusId): bool
    {
        if ($this->neronVersion->findByDaedalusIdOrThrow($daedalusId)->getMajor() < 5) {
            return false;
        }

        if (!$this->rebelBaseRepository->areAllRebelBasesDecoded($daedalusId)) {
            return false;
        }

        if (!$this->xylophEntryRepository->areAllXylophDatabasesDecoded($daedalusId)) {
            return false;
        }

        return true;
    }
}
