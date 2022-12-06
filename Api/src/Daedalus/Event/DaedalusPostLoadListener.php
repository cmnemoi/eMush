<?php

namespace Mush\Daedalus\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DaedalusPostLoadListener
{
    private CycleServiceInterface $cycleService;

    public function __construct(CycleServiceInterface $cycleService)
    {
        $this->cycleService = $cycleService;
    }

    public function postLoad(Daedalus $daedalus, LifecycleEventArgs $event): void
    {
        if (!in_array($daedalus->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::CURRENT])) {
            return;
        }

        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }

        $this->cycleService->handleCycleChange(new \DateTime(), $daedalus);
    }
}
