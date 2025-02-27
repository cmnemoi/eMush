<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Event\LinkWithSolKilledEvent;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;

final readonly class KillLinkWithSolService
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private EventServiceInterface $eventService,
        private LinkWithSolRepositoryInterface $linkWithSolRepository
    ) {}

    public function execute(int $daedalusId, int $successRate = 100, array $tags = []): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($daedalusId);

        if ($this->shouldAbortKillingLink($successRate, $linkWithSol)) {
            return;
        }

        $linkWithSol->unestablish();
        $this->linkWithSolRepository->save($linkWithSol);

        $this->eventService->callEvent(
            event: new LinkWithSolKilledEvent($daedalusId, tags: $tags),
            name: LinkWithSolKilledEvent::class
        );
    }

    private function shouldAbortKillingLink(int $successRate, LinkWithSol $linkWithSol): bool
    {
        return $this->d100Roll->isAFailure($successRate) || $linkWithSol->isNotEstablished();
    }
}
