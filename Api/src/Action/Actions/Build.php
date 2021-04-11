<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Build extends AbstractAction
{
    protected string $name = ActionEnum::BUILD;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gearToolService = $gearToolService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment && !$parameter instanceof Door;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::BLUEPRINT, 'groups' => ['visibility']]));
    }

    public function cannotExecuteReason(): ?string
    {
        //@TODO use validator
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);

        //Check the availlability of the ingredients
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            if ($this->gearToolService->getEquipmentsOnReachByName($this->player, $name)->count() < $number) {
                return ActionImpossibleCauseEnum::BUILD_LACK_RESSOURCES;
            }
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);

        $blueprintEquipment = $this->gameEquipmentService->createGameEquipment(
            $blueprintMechanic->getEquipment(),
            $this->player->getDaedalus()
        );

        // remove the used ingredients starting from the player inventory
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            for ($i = 0; $i < $number; ++$i) {
                if ($this->player->hasItemByName($name)) {
                    // @FIXME change to a random choice of the item
                    $ingredient = $this->player->getItems()
                        ->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->first();
                } else {
                    // @FIXME change to a random choice of the equipment
                    $ingredient = $this->player->getPlace()->getEquipments()
                        ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->first();
                }

                $equipmentEvent = new EquipmentEvent($ingredient, VisibilityEnum::HIDDEN);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
            }
        }

        $equipmentEvent = new EquipmentEvent($parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        //create the equipment
        $equipmentEvent = new EquipmentEvent($blueprintEquipment, VisibilityEnum::HIDDEN);
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($blueprintEquipment);

        $this->playerService->persist($this->player);

        return new Success($blueprintEquipment);
    }
}
