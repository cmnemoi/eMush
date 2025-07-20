<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;

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
        $this->changeFoodDestructionOption($neron);
        $neron->toggleVocodedAnnouncements();
        $neron->toggleDeathAnnouncements();
        $this->toggleMagneticNet($neron);
        $this->togglePlasmaShield($neron);
    }

    private function toggleMagneticNet(Neron $neron): void
    {
        $daedalus = $neron->getDaedalusInfo()->getDaedalus();
        if ($daedalus?->hasActiveProject(ProjectName::MAGNETIC_NET)) {
            $neron->toggleMagneticNet();
        }
    }

    private function togglePlasmaShield(Neron $neron): void
    {
        $daedalus = $neron->getDaedalusInfo()->getDaedalus();
        if ($daedalus?->hasActiveProject(ProjectName::MAGNETIC_NET)) {
            $neron->togglePlasmaShield();
        }
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

    private function changeFoodDestructionOption(Neron $neron): void
    {
        $neron->changeFoodDestructionOption($this->randomFoodDestructionOption($neron));
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

    private function randomFoodDestructionOption(Neron $neron): NeronFoodDestructionEnum
    {
        $currentOption = $neron->getFoodDestructionOption();
        $candidateOptions = NeronFoodDestructionEnum::getAllExcept($currentOption);

        return $this->randomService->getRandomElement($candidateOptions);
    }
}
