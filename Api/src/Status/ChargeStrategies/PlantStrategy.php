<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlantStrategy extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::GROWING_PLANT;

    public function __construct(StatusServiceInterface $statusService)
    {
        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        $status = $this->statusService->updateCharge($status, 1, $reasons, $time);

        // if the plant reached the number of cycles required to mature, remove the status
        if ($status->getVariableByName($status->getName())->isMax()) {
            $this->statusService->removeStatus(
                EquipmentStatusEnum::PLANT_YOUNG,
                $status->getOwner(),
                $reasons,
                $time,
                VisibilityEnum::PUBLIC
            );

            return null;
        }

        return $status;
    }
}
