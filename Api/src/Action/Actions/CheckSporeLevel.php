<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckSporeLevel extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CHECK_SPORE_LEVEL;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
        private RoomLogServiceInterface $roomLogService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_USED_MYCOSCAN,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::DAILY_LIMIT_MYCOSCAN]),
        ]);
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
        $this->createMycoscanCheckLog();
        $this->createHasUsedMycoscanStatus();
    }

    private function createMycoscanCheckLog(): void
    {
        $player = $this->player;

        if ($player->isMush()) {
            $nbSpores = 0;
        } else {
            $nbSpores = $player->getVariableValueByName(PlayerVariableEnum::SPORE);
        }

        $this->roomLogService->createLog(
            logKey: ActionLogEnum::CHECK_SPORE_LEVEL,
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'actions_log',
            player: $this->player,
            parameters: [
                'quantity' => $nbSpores,
            ],
        );
    }

    private function createHasUsedMycoscanStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_USED_MYCOSCAN,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
