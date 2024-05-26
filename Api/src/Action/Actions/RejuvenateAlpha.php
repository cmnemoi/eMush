<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RejuvenateAlpha extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::REJUVENATE;

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
        $metadata->addConstraint(
            new HasRole([
                'roles' => [RoleEnum::SUPER_ADMIN, RoleEnum::ADMIN],
                'groups' => ['visibility'],
            ])
        );
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
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::HEALTH_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::MORAL_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::ACTION_POINT);
        $this->dispatchSetToMaxEvent(PlayerVariableEnum::MOVEMENT_POINT);

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::HAS_REJUVENATED,
            $this->player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
    }

    private function dispatchSetToMaxEvent(string $variable): void
    {
        $maxValue = $this->player->getVariableByName($variable)->getMaxValue();

        if ($maxValue === null) {
            throw new \LogicException("{$variable} should have a maximum value");
        }

        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            $variable,
            $maxValue,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::SET_VALUE);
    }
}
