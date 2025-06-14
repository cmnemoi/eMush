<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Cure Cat" action (curing the cat of the infected status).
 * This action is granted by the Retro Fungal Serum.
 */
final class CureCat extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::CURE_CAT;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::INVENTORY,
            'groups' => [ClassConstraint::VISIBILITY]]));

        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ToolItemEnum::RETRO_FUNGAL_SERUM],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->isSchrodinger();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result->isASuccess()) {
            $this->removeCatInfectedStatus();
            $this->destroySerum();
        }

        $this->putCatInPlayerRoom();
    }

    private function removeCatInfectedStatus(): void
    {
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $this->gameEquipmentTarget(),
            tags: $this->getActionConfig()->getActionTags(),
            time: new \DateTime(),
        );
    }

    private function destroySerum()
    {
        $serum = $this->getPlayer()->getEquipmentByNameOrThrow(ToolItemEnum::RETRO_FUNGAL_SERUM);

        $equipmentEvent = new InteractWithEquipmentEvent(
            $serum,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime()
        );

        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function putCatInPlayerRoom(): void
    {
        $tags = $this->getTags();
        $tags[] = $this->gameItemTarget()->getName();

        $itemEvent = new MoveEquipmentEvent(
            equipment: $this->gameItemTarget(),
            newHolder: $this->player->getPlace(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $tags,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($itemEvent, EquipmentEvent::CHANGE_HOLDER);
    }
}
