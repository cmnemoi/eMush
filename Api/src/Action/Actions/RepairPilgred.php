<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\NoEfficiency;
use Mush\Action\Validator\ProjectFinished;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\UseCase\AdvanceProjectUseCase;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new ProjectFinished(['groups' => ['visibility']]));
        $metadata->addConstraint(new NoEfficiency(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NO_EFFICIENCY]));
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

        $projectEvent = new ProjectEvent(
            $project,
            author: $this->player,
            tags: $this->getAction()->getActionTags(),
        );

        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_ADVANCED);
    }
}
