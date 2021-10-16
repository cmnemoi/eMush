<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\EquipmentReachable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Transplant extends AbstractAction
{
    protected string $name = ActionEnum::TRANSPLANT;

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

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new EquipmentReachable(['name' => ItemEnum::HYDROPOT, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        //@TODO fail transplant
        /** @var Fruit $fruitType */
        $fruitType = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        /** @var GameItem $hydropot */
        $hydropot = $this->gearToolService->getEquipmentsOnReachByName($this->player, ItemEnum::HYDROPOT)->first();

        /** @var Place $place */
        $place = $hydropot->getPlace() ?? $hydropot->getPlayer();

        /** @var GameItem $plantEquipment */
        $plantEquipment = $this->gameEquipmentService
                    ->createGameEquipmentFromName($fruitType->getPlantName(), $this->player->getDaedalus());

        if ($place instanceof Player) {
            $plantEquipment->setPlayer($place);
        } else {
            $plantEquipment->setPlace($place);
        }

        $equipmentEvent = new EquipmentEvent(
            $parameter,
            $place,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime());
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new EquipmentEvent(
            $hydropot,
            $place,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->gameEquipmentService->persist($plantEquipment);

        $this->playerService->persist($this->player);

        $success = new Success();

        return $success->setEquipment($plantEquipment);
    }
}
