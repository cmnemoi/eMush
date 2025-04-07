<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Guard extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::GUARD;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new PlaceType([
                'type' => PlaceTypeEnum::ROOM,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::NOT_A_ROOM,
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::GUARDIAN,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['visibility'],
            ])
        );
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
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GUARDIAN,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
