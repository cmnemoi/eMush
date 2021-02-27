<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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

class ExpressCook extends AbstractAction
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
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

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach());
        //            ($this->parameter->getEquipment()->getName() === GameRationEnum::STANDARD_RATION ||
        //            $this->parameter->getStatusByName(EquipmentStatusEnum::FROZEN)) &&
        //            $this->gearToolService->getUsedTool($this->player, $this->action->getName()) !== null
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->parameter->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService->createGameEquipmentFromName(GameRationEnum::COOKED_RATION, $this->player->getDaedalus());
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
        } elseif ($frozenStatus = $this->parameter->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $this->parameter->removeStatus($frozenStatus);
            $this->gameEquipmentService->persist($this->parameter);
        }

        //@TODO add effect on the link with sol

        $this->playerService->persist($this->player);

        return new Success();
    }
}
