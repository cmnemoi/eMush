<?php

declare(strict_types=1);

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * Resets status charge to its minimum value (e.g. zero) on a new day.
 */
final class DailyDecrementReset extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_DECREMENT_RESET;

    public function __construct(StatusServiceInterface $statusService)
    {
        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        if (!\in_array(EventEnum::NEW_DAY, $reasons, true)) {
            return $status;
        }

        $currentValue = $status->getCharge();
        $minValue = $status->getVariableByName($status->getName())->getMinValueOrThrow();

        return $this->statusService->updateCharge(
            chargeStatus: $status,
            delta: $minValue - $currentValue,
            tags: $reasons,
            time: $time
        );
    }
}
