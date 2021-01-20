<?php

namespace Mush\Status\Entity;

use Doctrine\Common\Collections\Collection;

trait TargetStatusTrait
{
    public function getStatuses(): Collection
    {
        return $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getOwner()) && $statusOwner->getOwner() === $this)
            ->map(fn (StatusTarget $statusTarget) => $statusTarget->getOwner())
            ;
    }

    public function getTargetingStatuses(): Collection
    {
        return $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getTarget()) && $statusOwner->getTarget() === $this)
            ->map(fn (StatusTarget $statusTarget) => $statusTarget->getTarget())
            ;
    }

    public function getStatusByName(string $name): ?Status
    {
        $status = $this->getStatuses()->filter(fn (Status $status) => ($status->getName() === $name))->first();

        return $status ? $status : null;
    }

    public function hasStatus(string $statusName): bool
    {
        return $this->getStatuses()->exists(fn ($key, Status $status) => ($status->getName() === $statusName));
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
            if ($statusTarget = $status->getStatusTargetOwner()) {
                $this->statuses->removeElement($statusTarget);
                $statusTarget->removeStatusLinksTarget();
            }
            if ($statusTarget = $status->getTarget()) {
                $statusTarget->removeStatus($status);
                if ($targetTarget = $status->getStatusTargetTarget()) {
                    $targetTarget->removeStatusLinksTarget();
                }
            }
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
}
