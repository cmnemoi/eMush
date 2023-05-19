<?php

namespace Mush\Disease\Service;

use Mush\Disease\SymptomHandler\AbstractSymptomHandler;

class SymptomHandlerService implements SymptomHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractSymptomHandler $symptomHandler): void
    {
        $this->strategies[$symptomHandler->getName()] = $symptomHandler;
    }

    public function getSymptomHandler(string $name): ?AbstractSymptomHandler
    {
        if (!isset($this->strategies[$name])) {
            return null;
        }

        return $this->strategies[$name];
    }
}
