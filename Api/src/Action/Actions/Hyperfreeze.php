<?php

namespace Mush\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Perishable;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hyperfreeze extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::HYPERFREEZE;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    protected StatusServiceInterface $statusService;

    private ArrayCollection $foodToTransformIntoStandardRation;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;

        $this->foodToTransformIntoStandardRation = new ArrayCollection([
            GameRationEnum::COOKED_RATION,
            GameRationEnum::ALIEN_STEAK,
        ]);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Perishable(['groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::FROZEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
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
        /** @var GameEquipment $food */
        $food = $this->target;
        $time = new \DateTime();

        if ($this->foodToTransformIntoStandardRation->contains($food->getName())) {
            $isFoodDecomposing = $food->isDecomposing();
            $decompositionStatusName = $food->getDecompositionStatusNameOrEmptyString();

            $ration = $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
                GameRationEnum::STANDARD_RATION,
                $food,
                $this->player,
                $this->getActionConfig()->getActionTags(),
                $time,
                VisibilityEnum::PUBLIC
            );

            if ($isFoodDecomposing) {
                $this->statusService->createStatusFromName(
                    statusName: $decompositionStatusName,
                    holder: $ration,
                    tags: $this->getActionConfig()->getActionTags(),
                    time: $time
                );
            }
        } else {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::FROZEN,
                $food,
                $this->getActionConfig()->getActionTags(),
                $time
            );
        }
    }
}
