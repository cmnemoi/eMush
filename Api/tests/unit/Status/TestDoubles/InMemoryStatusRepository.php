<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Status\TestDoubles;

use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Repository\StatusRepositoryInterface;

final class InMemoryStatusRepository implements StatusRepositoryInterface
{
    /** @var Status[] */
    private array $statuses = [];

    public function findByCriteria(StatusCriteria $criteria): array
    {
        return array_filter(
            $this->statuses,
            static fn (Status $status) => $status->getStatusConfig()->getStatusName() === $criteria->getName()
            && $status->getOwner()->getDaedalus()->equals($criteria->getDaedalus())
        );
    }

    public function findByTargetAndName(StatusHolderInterface $target, string $name): ?Status
    {
        $result = array_filter(
            $this->statuses,
            static fn (Status $status) => $status->getTarget() === $target && $status->getStatusConfig()->getStatusName() === $name
        );
        if (\count($result) === 0) {
            return null;
        }

        return current($result);
    }

    public function findAllByName(string $name): array
    {
        return array_filter(
            $this->statuses,
            static fn (Status $status) => $status->getStatusConfig()->getStatusName() === $name
        );
    }

    public function save(Status $status): void
    {
        $this->statuses[$status->getStatusConfig()->getId() . '-' . $status->getOwner()->getId()] = $status;
    }
}
