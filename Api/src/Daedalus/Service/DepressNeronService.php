<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

final class DepressNeronService implements DepressNeronServiceInterface
{
    public function __construct(
        private ChangeNeronCrewLockUseCase $changeNeronCrewLock,
        private NeronServiceInterface $neronService,
        private RandomServiceInterface $randomService,
    ) {}

    public function execute(Neron $neron, ?Player $author = null, array $tags = [], \DateTime $time = new \DateTime()): void
    {
        $this->changeNeronCpuPriority($neron, $author, $tags);
        $this->changeCrewLock($neron);
        $this->changeInhibition($neron);
    }

    private function changeNeronCpuPriority(Neron $neron, ?Player $player, array $tags): void
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

    private function changeInhibition(Neron $neron): void
    {
        if ($neron->isInhibited()) {
            return;
        }

        $this->neronService->toggleInhibition($neron);
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
