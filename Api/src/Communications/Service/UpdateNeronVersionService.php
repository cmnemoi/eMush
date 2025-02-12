<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Event\NeronVersionUpdatedEvent;
use Mush\Communications\Repository\NeronVersionRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;

final readonly class UpdateNeronVersionService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private NeronMinorVersionIncrementServiceInterface $neronMinorVersionIncrement,
        private NeronVersionRepositoryInterface $neronVersionRepository,
    ) {}

    public function execute(int $daedalusId): bool
    {
        $majorUpdated = $this->incrementNeronVersionForDaedalus($daedalusId);

        $this->eventService->callEvent(
            event: new NeronVersionUpdatedEvent($daedalusId, $majorUpdated),
            name: NeronVersionUpdatedEvent::class,
        );

        return $majorUpdated;
    }

    public function incrementNeronVersionForDaedalus(int $daedalusId): bool
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalusId);

        $minorVersionIncrement = $this->neronMinorVersionIncrement->generateFrom($neronVersion->getMajor());
        $majorUpdated = $neronVersion->increment($minorVersionIncrement);

        $this->neronVersionRepository->save($neronVersion);

        return $majorUpdated;
    }
}
