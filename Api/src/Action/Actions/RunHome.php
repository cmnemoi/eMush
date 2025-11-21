<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\IsExploring;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RunHome extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::RUN_HOME;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ExplorationServiceInterface $explorationService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new IsExploring([
                'groups' => [ClassConstraint::VISIBILITY],
            ])
        );
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::LOST,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => [ClassConstraint::EXECUTE],
            'message' => ActionImpossibleCauseEnum::RUN_HOME_LOST,
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

        $this->explorationService->closeExploration($exploration, reasons: $this->getTags(), author: $this->player);
    }
}
