<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Specification\Mechanic;
use Mush\Action\Specification\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlant extends AbstractAction
{
    protected string $name = ActionEnum::TREAT_PLANT;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    protected function getVisibilitySpecifications(): array
    {
        return [
            Reach::class => null,
        ];
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED) === null) {
            return ActionImpossibleCauseEnum::TREAT_PLANT_NO_DISEASE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        if ($diseased = $this->parameter->getStatusByName(EquipmentStatusEnum::PLANT_DISEASED)) {
            $this->parameter->removeStatus($diseased);
            $this->gameEquipmentService->persist($this->parameter);
        }

        return new Success();
    }
}
