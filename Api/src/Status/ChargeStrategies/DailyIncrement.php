<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
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

    public function apply(ChargeStatus $status): void
    {
        $daedalus = $this->getDaedalus($status);
        //Only applied on cycle 1
        if ($daedalus->getCycle() !== 1 ||
            ($status->getThreshold() !== null && $status->getCharge() >= $status->getThreshold())
        ) {
            return;
        }
        $status->addCharge(1);
    }

    private function getDaedalus(Status $status): Daedalus
    {
        if ($player = $status->getPlayer()) {
            return $player->getDaedalus();
        }
        if ($room = $status->getRoom()) {
            return $room->getDaedalus();
        }
        if ($equipment = $status->getGameEquipment()) {
            if ($room = $equipment->getRoom()) {
                return $room->getDaedalus();
            }
            if ($equipment instanceof GameItem && ($player = $equipment->getPlayer())) {
                return $player->getDaedalus();
            }
        }

        throw new \LogicException('status has no properties');
    }
}
