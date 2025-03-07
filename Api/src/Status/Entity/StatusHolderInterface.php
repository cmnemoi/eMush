<?php

namespace Mush\Status\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

interface StatusHolderInterface
{
    public function getStatuses(): Collection;

    public function getTargetingStatuses(): Collection;

    public function getStatusByName(string $name): ?Status;

    public function hasStatus(string $statusName): bool;

    public function hasTargetingStatus(string $statusName): bool;

    public function setStatuses(Collection $statuses): self;

    public function addStatus(Status $status): self;

    public function removeStatus(Status $status): self;

    public function getClassName(): string;

    public function getDaedalus(): Daedalus;

    public function getId(): int;

    public function getName(): string;

    public function getStatusByNameAndTarget(string $name, self $target): ?Status;

    public function equals(self $statusHolder): bool;

    public function notEquals(self $statusHolder): bool;

    public function getPlace(): Place;

    public function getChargeStatusByName(string $name): ?ChargeStatus;
}
