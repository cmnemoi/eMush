<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Event\LinkWithSolKilledEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;

final readonly class KillLinkWithSolService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private LinkWithSolRepositoryInterface $linkWithSolRepository
    ) {}

    public function execute(int $daedalusId): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalusId);
        $linkWithSol->unestablish();
        $this->linkWithSolRepository->save($linkWithSol);

        $this->eventService->callEvent(
            event: new LinkWithSolKilledEvent($daedalusId),
            name: LinkWithSolKilledEvent::class
        );
    }
}
