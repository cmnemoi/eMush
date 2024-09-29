<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Take_cat extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TAKE_CAT;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::SHELVE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'room', 'message' => ActionImpossibleCauseEnum::NO_SHELVING_UNIT]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    public function getTags(): array
    {
        $tags = parent::getTags();

        /** @var GameItem $target */
        $target = $this->target;
        $tags[] = $target->getName();
        if ($target->hasStatus(EquipmentStatusEnum::HEAVY)) {
            $tags[] = EquipmentStatusEnum::HEAVY;
        }

        return $tags;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->putItemInPlayerInventory();

    }

    private function putItemInPlayerInventory(): void
    {
        $tags = $this->getTags();
        $tags[] = $this->gameItemTarget()->getName();

        $itemEvent = new MoveEquipmentEvent(
            equipment: $this->gameItemTarget(),
            newHolder: $this->player,
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $tags,
            time: new \DateTime(),
        );
        $this->eventService->callEvent($itemEvent, EquipmentEvent::CHANGE_HOLDER);
    }
}
