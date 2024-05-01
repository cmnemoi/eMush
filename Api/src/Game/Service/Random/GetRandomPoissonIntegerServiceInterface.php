<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

interface GetRandomPoissonIntegerServiceInterface
{
    public function execute(float $lambda): int;
}