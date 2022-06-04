<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveSpore extends AbstractAction
{
    protected string $name = ActionEnum::REMOVE_SPORE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::MUSH_REMOVE_SPORE]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function applyEffects(): ActionResult
    {
        $playerModifierEvent = new PlayerModifierEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            -3,
            $this->getActionName(),
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        if ($this->player->getStatusByName(PlayerStatusEnum::IMMUNIZED)) {
            return new Fail();
        }

        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        $nbSpores = $sporeStatus->getCharge();

        if ($nbSpores > 0) {
            $sporeStatus = $this->statusService->updateCharge($sporeStatus, -1);

            if ($sporeStatus === null) {
                throw new Error('Player should have a spore status');
            }

            $this->statusService->persist($sporeStatus);

            return new Success();
        } else {
            return new Fail();
        }
    }
}
