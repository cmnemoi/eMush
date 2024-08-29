<?php

namespace Mush\Status\Entity;

use Doctrine\Common\Collections\Collection;

trait TargetStatusTrait
{
    public function getStatuses(): Collection
    {
        return $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getOwner()) && $statusOwner->getOwner() === $this)
            ->map(static fn (StatusTarget $statusTarget) => $statusTarget->getOwner());
    }

    public function getTargetingStatuses(): Collection
    {
        return $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getTarget()) && $statusOwner->getTarget() === $this)
            ->map(static fn (StatusTarget $statusTarget) => $statusTarget->getTarget());
    }

    public function getStatusByName(string $name): ?Status
    {
        $status = $this->getStatuses()->filter(static fn (Status $status) => ($status->getName() === $name))->first();

        return $status ?: null;
    }

    public function getStatusByNameOrThrow(string $name): Status
    {
        $status = $this->getStatusByName($name);
        if (!$status) {
            throw new \RuntimeException("Status {$name} not found for {$this->getClassName()} {$this->getName()}");
        }

        return $status;
    }

    public function getChargeStatusByName(string $name): ?ChargeStatus
    {
        $status = $this->getStatusByName($name);

        return $status instanceof ChargeStatus ? $status : null;
    }

    public function getChargeStatusByNameOrThrow(string $name): ChargeStatus
    {
        $status = $this->getChargeStatusByName($name);
        if (!$status) {
            throw new \RuntimeException("Charge status {$name} not found for {$this->getClassName()} {$this->getName()}");
        }

        return $status;
    }

    public function getStatusByNameAndTarget(string $name, StatusHolderInterface $target): ?Status
    {
        $status = $this->getStatuses()
            ->filter(static fn (Status $status) => ($status->getName() === $name && $status->getTarget() === $target))
            ->first();

        return $status ?: null;
    }

    public function hasStatus(string $statusName): bool
    {
        return $this->getStatuses()->exists(static fn ($key, Status $status) => ($status->getName() === $statusName));
    }

    public function hasAnyStatuses(Collection $statuses): bool
    {
        return $this->getStatuses()->exists(static fn ($key, Status $status) => $statuses->contains($status->getName()));
    }

    public function hasTargetingStatus(string $statusName): bool
    {
        return $this->getTargetingStatuses()->exists(static fn ($key, Status $status) => ($status->getName() === $statusName));
    }

    /**
     * @return static
     */
    public function setStatuses(Collection $statuses): self
    {
        foreach ($statuses as $status) {
            $this->addStatus($status);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeStatus(Status $status): self
    {
        $statuses = $this->getStatuses();
        if ($statuses->contains($status)) {
            $this->statuses->removeElement($status->getStatusTargetOwner());

            if ($statusTarget = $status->getTarget()) {
                $statusTarget->removeStatus($status);
            }
            $status->delete();
        }

        return $this;
    }

    public function addStatusTarget(StatusTarget $statusTarget): self
    {
        if (!$this->statuses->contains($statusTarget)) {
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function equals(StatusHolderInterface $statusHolder): bool
    {
        return $this->getId() === $statusHolder->getId();
    }

    public function notEquals(StatusHolderInterface $statusHolder): bool
    {
        return $this->getId() !== $statusHolder->getId();
    }
}
