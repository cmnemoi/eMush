<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\IsRoom;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Drop extends AbstractAction
{
    protected string $name = ActionEnum::DROP;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function LoadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::INVENTORY, 'groups' => ['visibility']]));
        $metadata->addConstraint(new IsRoom(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NO_SHELVING_UNIT]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $parameter->setHolder($this->player->getPlace());

        $this->gameEquipmentService->persist($parameter);

        // Remove BURDENED status if no other heavy item in the inventory
        if ($this->player->hasStatus(PlayerStatusEnum::BURDENED) &&
            $this->player->getEquipments()->filter(function (GameItem $item) {
                return $item->hasStatus(EquipmentStatusEnum::HEAVY);
            })->isEmpty()
        ) {
            $statusEvent = new StatusEvent(
                PlayerStatusEnum::BURDENED,
                $parameter,
                $this->getActionName(),
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);
        }

        return new Success();
    }
}
