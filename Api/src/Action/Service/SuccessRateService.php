<?php

namespace Mush\Action\Service;

class SuccessRateService implements SuccessRateServiceInterface
{
    public const MAX_PERCENT = 99;

    public function getSuccessRate(
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
        float $fixedModificator = 0
    ): int {
        return (int) min(
            ($baseRate * (1.25) ** $numberOfAttempt) * $relativeModificator + $baseRate * $fixedModificator,
            self::MAX_PERCENT
        );
    }
}
