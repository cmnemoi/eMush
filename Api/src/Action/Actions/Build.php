<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Build extends AbstractAction
{
    protected string $name = ActionEnum::BUILD;

    protected GearToolServiceInterface $gearToolService;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GearToolServiceInterface $gearToolService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->gearToolService = $gearToolService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment && !$target instanceof Door;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::BLUEPRINT, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function cannotExecuteReason(): ?string
    {
        // @TODO use validator
        /** @var GameEquipment $target */
        $target = $this->target;
        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $target->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);

        // Check the availability of the ingredients
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            if ($this->gearToolService->getEquipmentsOnReachByName($this->player, $name)->count() < $number) {
                return ActionImpossibleCauseEnum::BUILD_LACK_RESSOURCES;
            }
        }

        return parent::cannotExecuteReason();
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $target */
        $target = $this->target;
        $time = new \DateTime();

        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $target->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);

        // remove the used ingredients starting from the player inventory
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            for ($i = 0; $i < $number; ++$i) {
                if ($this->player->hasEquipmentByName($name)) {
                    // @FIXME change to a random choice of the item
                    $ingredient = $this->player->getEquipments()
                        ->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->first();
                } else {
                    // @FIXME change to a random choice of the equipment
                    $ingredient = $this->player->getPlace()->getEquipments()
                        ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->first();
                }

                $interactEvent = new InteractWithEquipmentEvent(
                    $ingredient,
                    $this->player,
                    VisibilityEnum::HIDDEN,
                    $this->getAction()->getActionTags(),
                    $time
                );

                $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
            }
        }

        $interactEvent = new InteractWithEquipmentEvent(
            $target,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getAction()->getActionTags(),
            $time
        );
        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // Create the equipment
        $blueprintResult = $this->gameEquipmentService->createGameEquipmentFromName(
            $blueprintMechanic->getCraftedEquipmentName(),
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
            VisibilityEnum::PRIVATE
        );
    }
}
