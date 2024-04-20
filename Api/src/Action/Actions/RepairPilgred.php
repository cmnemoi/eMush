<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\UseCase\AdvanceProjectUseCase;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RepairPilgred extends AbstractAction
{
    protected string $name = ActionEnum::REPAIR_PILGRED;

    private AdvanceProjectUseCase $advanceProjectUseCase;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        AdvanceProjectUseCase $advanceProjectUseCase
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->advanceProjectUseCase = $advanceProjectUseCase;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Project;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Project $project */
        $project = $this->target;

        $this->advanceProjectUseCase->execute($this->player, $project);
    }
}
