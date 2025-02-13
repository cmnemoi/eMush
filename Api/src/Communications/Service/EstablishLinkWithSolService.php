<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;

final readonly class EstablishLinkWithSolService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private LinkWithSolRepositoryInterface $linkWithSolRepository
    ) {}

    public function execute(int $daedalusId): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalusId);
        $linkWithSol->establish();
        $this->linkWithSolRepository->save($linkWithSol);

        $this->eventService->callEvent(
            event: new LinkWithSolEstablishedEvent($daedalusId),
            name: LinkWithSolEstablishedEvent::class
        );
    }
}
