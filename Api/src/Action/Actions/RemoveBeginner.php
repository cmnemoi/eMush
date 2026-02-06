<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PreMush;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RemoveBeginner extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::REMOVE_BEGINNER;

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
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::BEGINNER,
                'target' => HasStatus::PLAYER,
                'groups' => ['visibility'],
            ]),
            new PreMush([
                'isStarting' => true,
                'groups' => ['visibility'],
            ]),
            new HasRole([
                'roles' => [RoleEnum::ADMIN],
                'groups' => ['visibility'],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $beginnerStatus = $this->player->getStatusByNameOrThrow(PlayerStatusEnum::BEGINNER);

        $this->statusService->removeStatus(
            $beginnerStatus->getName(),
            $this->player,
            $this->getActionConfig()->getActionTags(),
            new \DateTime(),
        );
    }
}
