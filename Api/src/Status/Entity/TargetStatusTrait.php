<?php

namespace Mush\Status\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Game\Enum\SkillEnum;

trait TargetStatusTrait
{
    public function getStatuses(): Collection
    {
        $statuses = $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getOwner()) && $statusOwner->getOwner() === $this)
            ->map(static fn (StatusTarget $statusTarget) => $statusTarget->getOwner());

        // temporary filter to exclude PoC skills
        /** @var Status $status */
        foreach ($statuses as $status) {
            if (SkillEnum::getAll()->contains($status->getName())) {
                $statuses->removeElement($status);
            }
        }

        return $statuses;
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

    /**
     * Temporary method for PoC skills.
     */
    public function getSkills(): Collection
    {
        $statuses = $this->statuses
            ->filter(fn (StatusTarget $statusTarget) => ($statusOwner = $statusTarget->getOwner()) && $statusOwner->getOwner() === $this)
            ->map(static fn (StatusTarget $statusTarget) => $statusTarget->getOwner());

        // temporary filter to get only PoC skills
        /** @var Status $status */
        foreach ($statuses as $status) {
            if (!SkillEnum::getAll()->contains($status->getName())) {
                $statuses->removeElement($status);
            }
        }

        return $statuses;
    }

    /**
     * Temporary method for PoC skills.
     */
    public function getSkillByName(string $name): ?Status
    {
        $status = $this->getSkills()->filter(static fn (Status $status) => ($status->getName() === $name))->first();

        return $status ?: null;
    }

    /**
     * Temporary method for PoC skills.
     */
    public function hasSkill(string $statusName): bool
    {
        return $this->getSkills()->exists(static fn ($key, Status $status) => ($status->getName() === $statusName));
    }
}
