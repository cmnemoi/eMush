<?php

namespace Mush\Action\Service;

class ActionService implements ActionServiceInterface
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
