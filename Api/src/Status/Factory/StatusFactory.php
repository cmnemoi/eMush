<?php

declare(strict_types=1);

namespace Mush\Status\Factory;

use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\StatusEnum;

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

    public static function createAttemptStatusForHolderAndAction(StatusHolderInterface $holder, string $action): Attempt
    {
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setStartCharge(0)
            ->buildName(GameConfigEnum::DEFAULT);

        $attempt = new Attempt($holder, $attemptConfig);
        $attempt->setAction($action);

        return $attempt;
    }
}
