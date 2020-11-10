<?php


namespace Mush\Action\Service;


interface SuccessRateServiceInterface
{
    public function getSuccessRate(int $baseRate, int $numberOfAttempt, int $modificator): int;
}