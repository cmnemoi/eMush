<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\EventServiceInterface;

final readonly class EstablishLinkWithSolService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private LinkWithSolRepositoryInterface $linkWithSolRepository
    ) {}

    public function execute(Daedalus $daedalus): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalus->getId());
        $linkWithSol->establish();
        $this->linkWithSolRepository->save($linkWithSol);

        $this->eventService->callEvent(
            event: new LinkWithSolEstablishedEvent($daedalus),
            name: LinkWithSolEstablishedEvent::class
        );
    }
}
