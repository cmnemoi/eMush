<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Perishable;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Action\Validator\UsedTool;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hyperfreeze extends AbstractAction
{
    protected string $name = ActionEnum::HYPERFREEZE;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gearToolService = $gearToolService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::RATION, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Reach(['groups' => ['visibility']]));
        $metadata->addConstraint(new Perishable(['groups' => ['visibility']]));
        $metadata->addConstraint(new UsedTool(['groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => EquipmentStatusEnum::FROZEN, 'contain' => false, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->parameter->getEquipment()->getName() === GameRationEnum::COOKED_RATION ||
            $this->parameter->getEquipment()->getName() === GameRationEnum::ALIEN_STEAK) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService
                ->createGameEquipmentFromName(GameRationEnum::STANDARD_RATION, $this->player->getDaedalus())
            ;
            $equipmentEvent = new EquipmentEvent($newItem, VisibilityEnum::HIDDEN);
            $equipmentEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

            foreach ($this->parameter->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setGameEquipment($newItem);
                $this->statusService->persist($status);
            }

            $equipmentEvent = new EquipmentEvent($this->parameter, VisibilityEnum::HIDDEN);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

            $this->gameEquipmentService->persist($newItem);
        } else {
            $this->statusService->createCoreStatus(
                EquipmentStatusEnum::FROZEN,
                $this->parameter
            );

            $this->gameEquipmentService->persist($this->parameter);
        }

        $this->playerService->persist($this->player);

        return new Success();
    }
}
