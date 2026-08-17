<?php

declare(strict_types=1);

namespace Mush\Action\Actions\SummerEventActions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Open" action on containers.
 * This action is granted by Survival Kit, Lunchbox, Coffee Thermos, Christmas Gifts.
 */
class OpenTreasure extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::OPEN_TREASURE;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        RoomLogServiceInterface $roomLogService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->roomLogService = $roomLogService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        foreach ($this->getTreasureContents() as $equipmentName) {
            $this->createContent($equipmentName);
        }
        $this->destroyEmptyContainer();
    }

    private function getTreasureContents(): array
    {
        return [
            ItemEnum::ARTEFACT_GENERIC,
            ItemEnum::ARTEFACT_GENERIC,
            ItemEnum::MAGEBOOK_GENERIC,
            ItemEnum::MAGEBOOK_GENERIC,
            ItemEnum::TREASURE_HUNT_CHEST_OPENED,
            ItemEnum::STARMAP_FRAGMENT,
        ];
    }

    private function createContent(string $equipmentName): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $equipmentName,
            equipmentHolder: $this->player,
            visibility: VisibilityEnum::PUBLIC,
            time: new \DateTime(),
            reasons: $this->getTags(),
        );
    }

    private function destroyEmptyContainer(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->gameEquipmentTarget(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
