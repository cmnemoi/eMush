<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class WaterPlant extends AbstractAction
{
    protected string $name = ActionEnum::WATER_PLANT;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ParameterHasAction());
        $metadata->addConstraint(new Reach());
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) === null &&
            $this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT) === null
        ) {
            return ActionImpossibleCauseEnum::TREAT_PLANT_NO_DISEASE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Status $status */
        $status = ($this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)
            ?? $this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT));

        $this->parameter->removeStatus($status);

        $this->gameEquipmentService->persist($this->parameter);

        return new Success();
    }
}
