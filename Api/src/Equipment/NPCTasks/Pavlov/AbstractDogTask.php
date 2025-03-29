<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;

abstract class AbstractDogTask
{
    protected ?self $nextTask = null;
    protected bool $taskNotApplicable = false;

    public function __construct(
        protected EventServiceInterface $eventService
    ) {}

    public function execute(GameEquipment $pavlov, \DateTime $time): void
    {
        // Apply current task effect.
        $this->applyEffect($pavlov, $time);

        // If current task is not applicable anymore, move to the next task.
        if ($this->taskNotApplicable && $this->thereIsANextTask()) {
            $this->nextTask?->execute($pavlov, $time);

            return;
        }
    }

    public function setNextDogTask(self $task): void
    {
        $this->nextTask = $task;
    }

    public function isApplicable(): bool
    {
        return $this->taskNotApplicable === false;
    }

    public function name(): string
    {
        return static::class;
    }

    abstract protected function applyEffect(GameEquipment $pavlov, \DateTime $time): void;

    private function thereIsANextTask(): bool
    {
        return $this->nextTask !== null;
    }
}
