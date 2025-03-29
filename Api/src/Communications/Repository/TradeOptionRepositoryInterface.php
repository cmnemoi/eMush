<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\TradeOption;

interface TradeOptionRepositoryInterface
{
    public function findByIdOrThrow(int $id): TradeOption;
}
