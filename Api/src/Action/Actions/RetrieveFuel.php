<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Fuel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\ParameterName;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RetrieveFuel extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_FUEL;

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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new ParameterName(['name' => EquipmentEnum::FUEL_TANK, 'groups' => ['visibility']]),
            new Fuel(['groups' => ['visibility']]),
            new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]),
            new HasStatus([
                'status' => EquipmentStatusEnum::BROKEN,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            ]),
        ]);
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment && !$parameter instanceof Door;
    }

    protected function applyEffects(): ActionResult
    {
        $item = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::FUEL_CAPSULE,
            $this->getPlayer()->getDaedalus()
        );

        if (!$item instanceof GameItem) {
            throw new \LogicException('invalid GameItem: [' . $item::class . '].');
        }

        $item->setPlayer($this->getPlayer());
        $this->gameEquipmentService->persist($item);

        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            -1,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_FUEL);

        return new Success();
    }
}
