<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;

class CycleIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_INCREMENT;

    public function __construct(StatusServiceInterface $statusService)
    {
        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status, Daedalus $daedalus): ?ChargeStatus
    {
        return $this->statusService->changeCharge($status, 1);
    }
}
