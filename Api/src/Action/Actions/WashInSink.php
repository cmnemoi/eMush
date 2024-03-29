<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AreShowersDismantled;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WashInSink extends AbstractAction
{
    protected string $name = ActionEnum::WASH_IN_SINK;

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

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach([
            'reach' => ReachEnum::ROOM,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new AreShowersDismantled([
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::ALREADY_WASHED_IN_SINK_TODAY,
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => EquipmentStatusEnum::BROKEN,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        if ($this->player->getStatusByName(PlayerStatusEnum::MUSH)) {
            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($this->player->getStatusByName(PlayerStatusEnum::DIRTY)) {
            $this->statusService->removeStatus(
                PlayerStatusEnum::DIRTY,
                $this->player,
                $this->getAction()->getActionTags(),
                new \DateTime(),
            );
        }

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK,
            $this->player,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
    }
}
