<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class implementing the "pirate radio" action.
 * This action is granted by the Radio Pirate skill. (@TODO).
 *
 * For 3 PA, "Pirate Radio" break the talkie or i-trackie of the targeted player.
 * additionally, pirate player have access to the channels of the pirated player
 * Targeted player still have access to the private channels where he can whisper
 * Targeted player still have access to the public channel if he has other means to talk (comms center, brainsync)
 * Pirate player do not have access to channels where the target player don't use his talkie (i.e. if he shares the same room that all other participant)
 * Effect last until the talkie is repaired
 *
 * Effect differs from original game
 * - pirate player can still talk with his own voice any time
 * - Speaking in general channel by other means is not enough to remove the effect, the talkie need to be repaired
 * - Pirate player have access to some private channels of the target
 *
 * More info on original behavior : http://mushpedia.com/wiki/Radio_Pirate
 */
class ScrewTalkie extends AbstractAction
{
    protected string $name = ActionEnum::SCREW_TALKIE;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::TALKIE_SCREWED,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SCREWED_TALKIE_ALREADY_PIRATED,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::WALKIE_TALKIE, ItemEnum::ITRACKIE],
            'contains' => true,
            'all' => false,
            'target' => HasEquipment::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SCREWED_TALKIE_NO_TALKIE,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        /** @var Item $talkie */
        $talkie = $parameter->getEquipments()->filter(fn (Item $item) => $item->getName() === ItemEnum::WALKIE_TALKIE ||
            $item->getName() === ItemEnum::ITRACKIE
        )->first();

        if (!$talkie->isBroken()) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::BROKEN,
                $talkie,
                $this->getActionName(),
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::TALKIE_SCREWED,
            $this->player,
            $this->getActionName(),
            new \DateTime()
        );
        $statusEvent->setStatusTarget($parameter);
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
