<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
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

    public function apply(ChargeStatus $status, Daedalus $daedalus): ?ChargeStatus
    {
        //@TODO: Handle garden

        return $this->statusService->updateCharge($status, 1);
    }
}
