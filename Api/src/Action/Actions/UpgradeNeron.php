<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\LinkWithSolConstraint;
use Mush\Action\Validator\NeedTitle;
use Mush\Action\Validator\NoMoreNeronProjects;
use Mush\Communications\Service\UpdateNeronVersionService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpgradeNeron extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::UPGRADE_NERON;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly UpdateNeronVersionService $updateNeronVersion,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'groups' => [ClassConstraint::VISIBILITY],
                'statusTargetName' => EquipmentEnum::COMMUNICATION_CENTER,
            ]),
            new NeedTitle([
                'title' => TitleEnum::COM_MANAGER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::COMS_NOT_OFFICER,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::DIRTY,
                'contain' => false,
                'target' => HasStatus::PLAYER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            ]),
            new NoMoreNeronProjects([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::MAX_NERON_VERSION_REACHED,
            ]),
            new LinkWithSolConstraint([
                'shouldBeEstablished' => true,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        $upgradeIsSuccessful = $this->updateNeronVersion->execute($this->player->getDaedalus()->getId());

        return $upgradeIsSuccessful ? new Success() : new Fail();
    }

    protected function applyEffect(ActionResult $result): void {}
}
