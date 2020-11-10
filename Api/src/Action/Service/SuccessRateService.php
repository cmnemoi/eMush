<?php


namespace Mush\Action\Service;

class SuccessRateService implements SuccessRateServiceInterface
{
    public function getSuccessRate(int $baseRate, int $numberOfAttempt, int $modificator): int
    {
        return ($baseRate * (1.25)**$numberOfAttempt) * (1 + 0.5 * $modificator);
    }
}