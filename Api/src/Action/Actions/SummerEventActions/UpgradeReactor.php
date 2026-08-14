<?php

declare(strict_types=1);

namespace Mush\Action\Actions\SummerEventActions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipmentOnBoard;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\HasStatusOrReachableEquipment;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Upgrade Reactor" action.
 * This action is granted by the emergency reactor if the treasure hunt tablet is on the ship.
 *
 * For 3 PA, make the travel toward the event planet available.
 */
class UpgradeReactor extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::UPGRADE_REACTOR;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::REACTOR_UPGRADED,
            'contain' => false,
            'target' => HasStatus::DAEDALUS,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasEquipmentOnBoard([
            'name' => ItemEnum::TREASURE_HUNT_TABLET,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatusOrReachableEquipment([
            'equipmentName' => ItemEnum::TREASURE_HUNT_DEVICE,
            'status' => PlayerStatusEnum::GENIUS_IDEA,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::NEED_GENIUS_OR_ALIEN_DEVICE,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET,
            $this->getPlayer()->getDaedalus(),
            $this->getTags(),
            new \DateTime()
        );
        $this->statusService->createStatusFromName(
            DaedalusStatusEnum::REACTOR_UPGRADED,
            $this->getPlayer()->getDaedalus(),
            $this->getTags(),
            new \DateTime()
        );
    }
}
