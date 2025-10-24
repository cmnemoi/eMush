<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Schrodinger;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;

abstract class AbstractCatTask
{
    protected ?self $nextTask = null;
    protected bool $taskNotApplicable = false;

    public function __construct(
        protected EventServiceInterface $eventService
    ) {}

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        // Apply current task effect.
        $this->applyEffect($NPC, $time);

        // If current task is not applicable anymore, move to the next task.
        if ($this->taskNotApplicable && $this->thereIsANextTask()) {
            $this->nextTask?->execute($NPC, $time);

            return;
        }
    }

    public function setNextCatTask(self $task): void
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

    abstract protected function applyEffect(GameEquipment $NPC, \DateTime $time): void;

    private function thereIsANextTask(): bool
    {
        return $this->nextTask !== null;
    }
}
