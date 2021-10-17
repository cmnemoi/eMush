<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum as EnumEquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Take extends AbstractAction
{
    protected string $name = ActionEnum::TAKE;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::SHELVE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $parameter->setPlace(null);
        $parameter->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($parameter->hasStatus(EquipmentStatusEnum::HEAVY) &&
            !$this->player->hasStatus(PlayerStatusEnum::BURDENED)
        ) {
            $statusEvent = new StatusEvent(PlayerStatusEnum::BURDENED, $this->player, $this->getActionName(), new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        if ($hiddenStatus = $parameter->getStatusByName(EnumEquipmentStatusEnum::HIDDEN)) {
            $parameter->removeStatus($hiddenStatus);
            $this->player->removeStatus($hiddenStatus);
        }

        $this->gameEquipmentService->persist($parameter);
        $this->playerService->persist($this->player);

        return new Success();
    }
}
