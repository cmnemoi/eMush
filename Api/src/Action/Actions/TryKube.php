<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TryKube extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TRY_KUBE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
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
        if (!$this->randomService->isSuccessful($this->getOutputQuantity())) {
            return;
        }

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::POINTLESS_PLAYER,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime()
        );
    }
}
