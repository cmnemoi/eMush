<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;

class DailyIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_INCREMENT;

    private CycleServiceInterface $cycleService;

    public function __construct(
        StatusServiceInterface $statusService,
        CycleServiceInterface $cycleService
    ) {
        $this->cycleService = $cycleService;

        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status, Daedalus $daedalus): ?ChargeStatus
    {
        //Only applied on cycle 1
        if ($daedalus->getCycle() !== 1) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1);
    }
}
