<?php

namespace Mush\Equipment\Service;

use Mush\Equipment\NPCTasks\AiHandler\AbstractAiHandler;

class AiHandlerService implements AiHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractAiHandler $aiHandler): void
    {
        $this->strategies[$aiHandler->getName()] = $aiHandler;
    }

    public function getAiHandler(string $mechanicName): ?AbstractAiHandler
    {
        if (!isset($this->strategies[$mechanicName])) {
            return null;
        }

        return $this->strategies[$mechanicName];
    }
}
