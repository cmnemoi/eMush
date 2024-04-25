<?php

declare(strict_types=1);

namespace Mush\Game\Service;

interface GetRandomIntegerServiceInterface
{
    public function execute(int $min, int $max): int;
}
