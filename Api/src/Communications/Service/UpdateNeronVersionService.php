<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Repository\NeronVersionRepositoryInterface;

final readonly class UpdateNeronVersionService
{
    public function __construct(
        private NeronMinorVersionIncrementServiceInterface $neronMinorVersionIncrement,
        private NeronVersionRepositoryInterface $neronVersionRepository,
    ) {}

    public function execute(int $daedalusId): void
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalusId);

        $minorVersionIncrement = $this->neronMinorVersionIncrement->generateFrom($neronVersion->getMajor());
        $neronVersion->increment($minorVersionIncrement);

        $this->neronVersionRepository->save($neronVersion);
    }
}
