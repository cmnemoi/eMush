<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpenCapsule extends AbstractAction
{
    private static array $capsuleContent = [
        ItemEnum::FUEL_CAPSULE => 1,
        ItemEnum::OXYGEN_CAPSULE => 1,
        ItemEnum::METAL_SCRAPS => 1,
        ItemEnum::PLASTIC_SCRAPS => 1,
    ];

    protected string $name = ActionEnum::OPEN;

    private RandomServiceInterface $randomService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameEquipment $parameter */
        $parameter = $this->parameter;

        //remove the space capsule
        $equipmentEvent = new EquipmentEvent(
            $parameter->getName(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setExistingEquipment($parameter);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        //Get the content
        $contentName = $this->randomService->getSingleRandomElementFromProbaArray(self::$capsuleContent);

        if ($this->player->getEquipments()->count() < $this->player->getCharacterConfig()->getGameConfig()->getMaxItemInInventory()) {
            $newHolder = $this->player;
        } else {
            $newHolder = $this->player->getPlace();
        }
        $capsuleContent = $this->gameEquipmentService->createGameEquipmentFromName(
            $contentName,
            $newHolder,
            $this->getActionName(),
            new \DateTime()
        );

        $success = new Success();

        return $success->setEquipment($capsuleContent);
    }
}
