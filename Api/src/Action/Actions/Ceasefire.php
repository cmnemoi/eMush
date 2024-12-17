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
use Mush\Action\Validator\PreMush;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Ceasefire extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CEASEFIRE;

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
        $metadata->addConstraint(
            new PlaceType([
                'type' => PlaceTypeEnum::ROOM,
                'groups' => ['visibility'],
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_CEASEFIRED,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::UNIQUE_ACTION,
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlaceStatusEnum::CEASEFIRE->toString(),
                'target' => HasStatus::PLAYER_ROOM,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::ALREADY_A_CEASEFIRE_IN_ROOM,
            ])
        );
        $metadata->addConstraint(new PreMush([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED]));
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
        $this->createCeasefireStatus();
        $this->createHasCeasefiredStatus();
    }

    private function createCeasefireStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::CEASEFIRE->toString(),
            holder: $this->player->getPlace(),
            tags: $this->getTags(),
            time: new \DateTime(),
            target: $this->player,
        );
    }

    private function createHasCeasefiredStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_CEASEFIRED,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
