<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Infect extends AbstractAction
{
    protected string $name = ActionEnum::INFECT;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'checkMode' => GameVariableLevel::IS_MIN,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::INFECT_NO_SPORE,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::MUSH,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::INFECT_MUSH,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::IMMUNIZED,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::INFECT_IMMUNE,
        ]));
        $metadata->addConstraint(new DailySporesLimit(['target' => DailySporesLimit::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_DAILY_LIMIT]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->target;

        $playerModifierEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::SPORE,
            1,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setAuthor($this->player);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            -1,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $this->statusService->updateCharge(
            $mushStatus,
            -1,
            $this->action->getActionTags(),
            new \DateTime()
        );
    }
}
