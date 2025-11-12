<?php

declare(strict_types=1);

namespace Mush\Status\Repository;

use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

interface StatusRepositoryInterface
{
    public function findByCriteria(StatusCriteria $criteria): array;

    public function findByTargetAndName(StatusHolderInterface $target, string $name): ?Status;

    public function findAllByName(string $name): array;
}
