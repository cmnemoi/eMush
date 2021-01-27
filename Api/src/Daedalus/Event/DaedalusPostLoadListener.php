<?php

namespace Mush\Daedalus\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;

class DaedalusPostLoadListener
{
    private CycleServiceInterface $cycleService;

    public function __construct(CycleServiceInterface $cycleService)
    {
        $this->cycleService = $cycleService;
    }

    public function postLoad(Daedalus $daedalus, LifecycleEventArgs $event): void
    {
        if ($daedalus->getGameStatus() === GameStatusEnum::FINISHED) {
            return;
        }

        $this->cycleService->handleCycleChange(new \DateTime(), $daedalus);
    }
}
