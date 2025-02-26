<?php

namespace Mush\Communications\Service;

use Mush\Communications\Event\RebelBaseStartedContactEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;

final readonly class TriggerNextRebelBaseContactService
{
    public function __construct(
        private EventServiceInterface $eventService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {}

    public function execute(int $daedalusId): void
    {
        $rebelBase = $this->rebelBaseRepository->findNextContactingRebelBase($daedalusId);
        if (!$rebelBase) {
            return;
        }

        $rebelBase->triggerContact();
        $this->rebelBaseRepository->save($rebelBase);

        $this->eventService->callEvent(
            event: new RebelBaseStartedContactEvent($daedalusId),
            name: RebelBaseStartedContactEvent::class,
        );
    }
}
