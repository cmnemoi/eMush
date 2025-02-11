<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Event\LinkWithSolCreatedEvent;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Game\Service\EventServiceInterface;

final readonly class CreateLinkWithSolForDaedalusService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private LinkWithSolRepository $linkWithSolRepository
    ) {}

    public function execute(int $daedalusId): void
    {
        $linkWithSol = new LinkWithSol($daedalusId);
        $this->linkWithSolRepository->save($linkWithSol);

        $this->eventService->callEvent(
            event: new LinkWithSolCreatedEvent($daedalusId),
            name: LinkWithSolCreatedEvent::class
        );
    }
}
