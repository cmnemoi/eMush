<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\NoEfficiency;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\TargetProjectFinished;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\UseCase\AdvanceProjectUseCase;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractParticipateAction extends AbstractAction
{
    protected AdvanceProjectUseCase $advanceProjectUseCase;

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
        // TODO add validator research is possible?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new TargetProjectFinished(['groups' => ['visibility']]));
        $metadata->addConstraint(new NoEfficiency(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NO_EFFICIENCY]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
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
            tags: $this->getActionConfig()->getActionTags(),
        );

        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_ADVANCED);
    }
}
