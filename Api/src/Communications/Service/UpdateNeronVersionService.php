<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\NeronVersion;
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

    public function execute(int $daedalusId, ?int $fixedIncrement = null): bool
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($daedalusId);
        $this->incrementNeronVersion($neronVersion, $fixedIncrement);

        $this->eventService->callEvent(
            event: new NeronVersionUpdatedEvent($daedalusId, $neronVersion->majorHasBeenUpdated()),
            name: NeronVersionUpdatedEvent::class,
        );

        return $neronVersion->majorHasBeenUpdated();
    }

    private function incrementNeronVersion(NeronVersion $neronVersion, ?int $fixedIncrement): void
    {
        $minorVersionIncrement = $fixedIncrement ?? $this->neronMinorVersionIncrement->generateFrom($neronVersion->getMajor());
        $neronVersion->increment($minorVersionIncrement);

        $this->neronVersionRepository->save($neronVersion);
    }
}
