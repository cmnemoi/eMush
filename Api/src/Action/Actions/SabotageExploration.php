<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\ExplorationAlreadySabotaged;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsExploring;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SabotageExploration extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SABOTAGE_EXPLORATION;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new IsExploring([
            'groups' => [ClassConstraint::VISIBILITY],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LOST,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::SABOTAGE_EXPLORATION_LOST,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::HAS_USED_TRAITOR_THIS_EXPEDITION,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::SABOTAGE_EXPLORATION_SPENT,
        ]));
        $metadata->addConstraint(new ExplorationAlreadySabotaged([
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::EXPLORATION_ALREADY_SABOTAGED,
        ]));
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
        $exploration = $this->player->getExplorationOrThrow();
        $exploration->setIsSabotaged(true);

        $tags = $this->getTags();
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::HAS_USED_TRAITOR_THIS_EXPEDITION,
            $this->player,
            $tags,
            new \DateTime(),
        );
    }
}
