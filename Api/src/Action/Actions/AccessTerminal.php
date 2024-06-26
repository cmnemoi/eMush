<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasNeededTitleForTerminal;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NeronCrewLock;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccessTerminal extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::ACCESS_TERMINAL;

    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'contain' => false,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasNeededTitleForTerminal([
            'allowAccess' => true,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::TERMINAL_ROLE_RESTRICTED,
        ]));
        $metadata->addConstraint(new NeronCrewLock([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK,
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
        /** @var GameEquipment $terminal */
        $terminal = $this->target;

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: $this->getActionConfig()->getActionTags(),
            time: new \DateTime(),
            target: $terminal,
        );
    }
}
