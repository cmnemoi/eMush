<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Status as StatusValidator;
use Mush\Equipment\Entity\GameEquipment;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LieDown extends AbstractAction
{
    protected string $name = ActionEnum::LIE_DOWN;

    /** @var GameEquipment */
    protected $parameter;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::LYING_DOWN, 'target' => StatusValidator::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::ALREADY_IN_BED,
        ]));
        $metadata->addConstraint(new StatusValidator([
            'status' => EquipmentStatusEnum::BROKEN, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
        ]));
        $metadata->addConstraint(new StatusValidator([
            'status' => PlayerStatusEnum::LYING_DOWN, 'ownerSide' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BED_OCCUPIED,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $lyingDownStatus = new Status($this->player);
        $lyingDownStatus
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setTarget($this->parameter)
        ;

        $this->statusService->persist($lyingDownStatus);

        return new Success();
    }
}
