<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
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
        // @TODO: Handle garden

        return $this->statusService->updateCharge($status, 1, $reasons, $time);
    }
}
