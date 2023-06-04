<?php

namespace Mush\Daedalus\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DaedalusPostLoadListener
{
    private CycleServiceInterface $cycleService;
    private DaedalusServiceInterface $daedalusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        CycleServiceInterface $cycleService,
        DaedalusServiceInterface $daedalusService,
        PlayerServiceInterface $playerService
    ) {
        $this->cycleService = $cycleService;
        $this->daedalusService = $daedalusService;
        $this->playerService = $playerService;
    }

    public function postLoad(Daedalus $daedalus, LifecycleEventArgs $event): void
    {
        if (!in_array($daedalus->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::CURRENT])) {
            $this->closeOldDaedalus($daedalus);

            return;
        }
        if ($daedalus->isCycleChange()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Daedalus changing cycle');
        }

        $this->cycleService->handleCycleChange(new \DateTime(), $daedalus);
    }

    private function closeOldDaedalus(Daedalus $daedalus): void
    {
        $daedalusIsFinished = $daedalus->getDaedalusInfo()->isDaedalusFinished();
        $finishDate = $daedalus->getFinishedAt();
        $now = new \DateTime();

        if ($finishDate === null || !$daedalusIsFinished) {
            return;
        }
        if ($finishDate->diff($now)->days < 7) {
            return;
        }

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            $this->playerService->endPlayer($player, '');
        }

        $this->daedalusService->closeDaedalus(
            $daedalus,
            reasons: ['daedalus_closed_after_1_week'],
            date: $now
        );
    }
}
