<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\Status;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RetrieveOxygen extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_OXYGEN;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Oxygen(['groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
        $metadata->addConstraint(new Status([
            'status' => EquipmentStatusEnum::BROKEN, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
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
