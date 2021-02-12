<?php

namespace Mush\Action\Service;

interface ActionServiceInterface
{
    public function getSuccessRate(
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
        float $fixedModificator = 0
    ): int;
}
