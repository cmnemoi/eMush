<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
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

    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
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

        /** @var Place $place */
        $place = $this->player->getPlace();

        //remove the space capsule
        $equipmentEvent = new EquipmentEvent(
            $parameter,
            $place,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        //Get the content
        $contentName = $this->randomService->getSingleRandomElementFromProbaArray(self::$capsuleContent);

        $contentEquipment = $this
            ->gameEquipmentService
            ->createGameEquipmentFromName($contentName, $this->player->getDaedalus())
        ;
        $equipmentEvent = new EquipmentEvent(
            $contentEquipment,
            $place,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime()
        );
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($contentEquipment);

        $success = new Success();
        $success->setEquipment($contentEquipment);

        return $success;
    }
}
