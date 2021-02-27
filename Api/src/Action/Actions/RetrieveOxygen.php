<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class RetrieveOxygen extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_OXYGEN;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ParameterHasAction());
        $metadata->addConstraint(new Reach());
        $metadata->addConstraint(new Oxygen());
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->parameter->isBroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        $gameConfig = $this->player->getDaedalus()->getGameConfig();
        if ($this->player->getItems()->count() >= $gameConfig->getMaxItemInInventory()) {
            return ActionImpossibleCauseEnum::FULL_INVENTORY;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $gameItem = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::OXYGEN_CAPSULE, $this->player->getDaedalus());

        if (!$gameItem instanceof GameItem) {
            throw new \LogicException('invalid GameItem');
        }

        $gameItem->setPlayer($this->player);

        $this->gameEquipmentService->persist($gameItem);

        $this->daedalusService->changeOxygenLevel($this->player->getDaedalus(), -1);

        $target = new Target($this->parameter->getName(), 'items');

        return new Success($target);
    }
}
