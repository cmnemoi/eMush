<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\ActionHistoryRevealLogService;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Premonition extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PREMONITION;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private ActionHistoryRevealLogService $actionHistoryRevealLog,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new GameVariableLevel([
                'variableName' => PlayerVariableEnum::MORAL_POINT,
                'checkMode' => GameVariableLevel::EQUALS,
                'target' => GameVariableLevel::PLAYER,
                'value' => 1,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::PREMONITION_INSUFFICIENT_MORALE,
            ])
        );
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->actionHistoryRevealLog->generate($this->numberOfActionsToReveal(), $this);
    }

    private function numberOfActionsToReveal(): int
    {
        return $this->getOutputQuantity();
    }
}
