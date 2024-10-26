<?php

declare(strict_types=1);

namespace Mush\Status\Factory;

use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\ConfigData\StatusConfigData;
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

        self::setupStatusConfigId($statusConfig);

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

        self::setupStatusConfigId($statusConfig);

        return new ChargeStatus($holder, $statusConfig);
    }

    public static function createChargeStatusFromStatusName(string $name, StatusHolderInterface $holder, ?int $charge = null): ChargeStatus
    {
        $statusData = StatusConfigData::getByStatusName($name);
        $statusConfig = ChargeStatusConfig::fromConfigData($statusData);

        self::setupStatusConfigId($statusConfig);

        $status = new ChargeStatus($holder, $statusConfig);
        if ($charge !== null) {
            $status->setCharge($charge);
        }

        return $status;
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

        self::setupStatusConfigId($attemptConfig);

        $attempt = new Attempt($holder, $attemptConfig);
        $attempt->setAction($action);

        return $attempt;
    }

    public static function createStatusByNameForHolderAndTarget(string $name, StatusHolderInterface $holder, StatusHolderInterface $target): Status
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName($name)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);

        $status = new Status($holder, $statusConfig);
        $status->setTarget($target);

        return $status;
    }

    private static function setupStatusConfigId(StatusConfig $statusConfig): void
    {
        (new \ReflectionProperty($statusConfig, 'id'))->setValue($statusConfig, $statusConfig->toHash());
    }
}
