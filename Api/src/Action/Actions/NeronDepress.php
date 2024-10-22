<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Service\NeronServiceInterface;
use Mush\Daedalus\UseCase\ChangeNeronCrewLockUseCase;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class NeronDepress extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::NERON_DEPRESS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ChangeNeronCrewLockUseCase $changeNeronCrewLock,
        private NeronServiceInterface $neronService,
        private RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->changeNeronCpuPriority();
        $this->changeCrewLock();
    }

    private function changeNeronCpuPriority(): void
    {
        $this->neronService->changeCpuPriority(
            $this->neron(),
            $this->randomCpuPriority(),
            $this->getTags(),
            $this->player,
        );
    }

    private function changeCrewLock(): void
    {
        $this->changeNeronCrewLock->execute($this->neron(), $this->randomCrewLock());
    }

    private function randomCpuPriority(): string
    {
        $neron = $this->neron();
        $currentPriority = $neron->getCpuPriority();
        $candidatePriorities = NeronCpuPriorityEnum::getAllExcept($currentPriority);

        return $this->randomService->getRandomElement($candidatePriorities);
    }

    private function randomCrewLock(): NeronCrewLockEnum
    {
        $neron = $this->neron();
        $currentLock = $neron->getCrewLock();
        $candidateLocks = NeronCrewLockEnum::getAllExcept($currentLock);

        return $this->randomService->getRandomElement($candidateLocks);
    }

    private function neron(): Neron
    {
        return $this->player->getDaedalus()->getNeron();
    }
}
