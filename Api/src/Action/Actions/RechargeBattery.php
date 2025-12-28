<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Action\Validator\HasStatus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RechargeBattery extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::RECHARGE_BATTERY;

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
            new HasRole([
                'roles' => [RoleEnum::SUPER_ADMIN],
                'groups' => ['visibility'],
            ]),
            new HasStatus([
                'status' => EquipmentStatusEnum::ELECTRIC_CHARGES,
                'target' => HasStatus::PARAMETER,
                'contain' => true,
                'groups' => ['visibility'],
            ]),
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
        $this->gameEquipmentTarget()->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->setMaxCharge(99)->setChargeToMax();
    }
}
