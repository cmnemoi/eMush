<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\GameEquipment;

abstract class AbstractAiHandler
{
    protected string $name;

    abstract public function execute(GameEquipment $NPC, \DateTime $time): void;

    public function getName(): string
    {
        return $this->name;
    }
}
