<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Daedalus\Repository\TitlePriorityRepositoryInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Putsch extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PUTSCH;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private TitlePriorityRepositoryInterface $titlePriorityRepository,
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
        $this->movePlayerToFirstPlaceForCommanderTitle();
    }

    private function movePlayerToFirstPlaceForCommanderTitle(): void
    {
        $commanderTitlePriority = $this->commanderTitlePriority();
        $commanderTitlePriority->movePlayerToFirstPlace($this->player);
        $this->titlePriorityRepository->save($commanderTitlePriority);
    }

    private function commanderTitlePriority(): TitlePriority
    {
        return $this->player->getDaedalus()->getTitlePriorityByNameOrThrow(TitleEnum::COMMANDER);
    }
}
