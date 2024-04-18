<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "pirate radio" action.
 * This action is granted by the Radio Pirate skill. (@TODO).
 *
 * For 3 PA, "Pirate Radio" break the talkie or i-trackie of the targeted player.
 * additionally, pirate player have access to the channels of the pirated player
 * Targeted player still have access to the private channels where he can whisper
 * Targeted player still have access to the public channel if he has other means to talk (comms center, brainsync)
 * Pirate player do not have access to channels where the target player don't use his talkie (i.e. if he shares the same room with all other participant)
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

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::WALKIE_TALKIE, ItemEnum::ITRACKIE],
            'contains' => true,
            'all' => false,
            'target' => HasEquipment::PARAMETER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SCREWED_TALKIE_NO_TALKIE,
        ]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ItemEnum::WALKIE_TALKIE, ItemEnum::ITRACKIE],
            'contains' => true,
            'all' => false,
            'target' => HasEquipment::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::SCREWED_TALKIE_NO_TALKIE,
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Player $target */
        $target = $this->target;

        /** @var GameItem $talkie */
        $talkie = $target->getEquipments()->filter(
            static fn (GameItem $item) => $item->getName() === ItemEnum::WALKIE_TALKIE
            || $item->getName() === ItemEnum::ITRACKIE
        )->first();

        if (!$talkie->isBroken()) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BROKEN,
                $talkie,
                $this->getAction()->getActionTags(),
                new \DateTime()
            );
        }

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::TALKIE_SCREWED,
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
            $target
        );
    }
}
