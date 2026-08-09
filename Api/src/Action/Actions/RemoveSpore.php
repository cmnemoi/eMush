<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\MushDamage;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveSpore extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::REMOVE_SPORE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::IMMUNIZED,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::IMMUNIZED_REMOVE_SPORE,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::SPORE_SUCKER_USED,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::SPORE_SUCKER_USED_TOO_RECENTLY,
            ]),
            new PlaceType([
                'groups' => [ClassConstraint::EXECUTE],
                'type' => PlaceTypeEnum::PLANET,
                'allowIfTypeMatches' => false,
                'message' => ActionImpossibleCauseEnum::ON_PLANET,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        $nbSpores = $this->player->getVariableValueByName(PlayerVariableEnum::SPORE);

        // type of actionResult determines which log to print
        if ($this->player->isMush()) {
            return new MushDamage();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            -3,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        // The player removes a spore if human
        if ($this->player->isHuman()) {
            $sporeLossEvent = new PlayerVariableEvent(
                $this->player,
                PlayerVariableEnum::SPORE,
                -1,
                $this->getActionConfig()->getActionTags(),
                new \DateTime(),
            );
            $this->eventService->callEvent($sporeLossEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        // add a status with a negative modifier that also prevent from used the spore sucker again for a few cycles.
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::SPORE_SUCKER_USED,
            $this->player,
            $this->getTags(),
            new \DateTime()
        );
    }
}
