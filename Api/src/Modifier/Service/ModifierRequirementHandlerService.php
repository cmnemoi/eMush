<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\ModifierRequirementHandler\AbstractModifierRequirementHandler;

class ModifierRequirementHandlerService implements ModifierRequirementHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractModifierRequirementHandler $modifierRequirementHandler): void
    {
        $this->strategies[$modifierRequirementHandler->getName()] = $modifierRequirementHandler;
    }

    public function getModifierRequirementHandler(ModifierActivationRequirement $requirement): ?AbstractModifierRequirementHandler
    {
        $strategyName = $requirement->getActivationRequirementName();

        if (!$strategyName || !isset($this->strategies[$strategyName])) {
            return null;
        }

        return $this->strategies[$strategyName];
    }
}
