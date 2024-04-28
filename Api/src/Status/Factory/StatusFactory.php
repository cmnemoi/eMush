<?php

declare(strict_types=1);

namespace Mush\Status\Factory;

use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

final class StatusFactory
{
    public static function createStatusByNameForHolder(string $name, StatusHolderInterface $holder): Status
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName($name)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);

        return new Status($holder, $statusConfig);
    }

    public static function createChargeStatusWithName(string $name, StatusHolderInterface $holder): ChargeStatus
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName($name)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);

        return new ChargeStatus($holder, $statusConfig);
    }
}
