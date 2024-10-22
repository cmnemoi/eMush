<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class DepressNeronService
{
    public function __construct(
        private NeronServiceInterface $neronService,
        private ChangeNeronCrewLockUseCase $changeNeronCrewLock,
        private RandomServiceInterface $randomService,
    ) {
    }

    public function execute(Neron $neron, Player $player, array $tags): void
    {
        $this->changeNeronCpuPriority($neron, $player, $tags);
        $this->changeCrewLock($neron);
    }

    private function changeNeronCpuPriority(Neron $neron, Player $player, array $tags): void
    {
        $this->neronService->changeCpuPriority(
            $neron,
            $this->randomCpuPriority($neron),
            $tags,
            $player,
        );
    }

    private function changeCrewLock(Neron $neron): void
    {
        $this->changeNeronCrewLock->execute($neron, $this->randomCrewLock($neron));
    }

    private function randomCpuPriority(Neron $neron): string
    {
        $currentPriority = $neron->getCpuPriority();
        $candidatePriorities = NeronCpuPriorityEnum::getAllExcept($currentPriority);

        return $this->randomService->getRandomElement($candidatePriorities);
    }

    private function randomCrewLock(Neron $neron): NeronCrewLockEnum
    {
        $currentLock = $neron->getCrewLock();
        $candidateLocks = NeronCrewLockEnum::getAllExcept($currentLock);

        return $this->randomService->getRandomElement($candidateLocks);
    }
}
