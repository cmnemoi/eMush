<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\ModifierHandler\AbstractModifierHandler;

class ModifierHandlerService implements ModifierHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractModifierHandler $modifierHandler): void
    {
        $this->strategies[$modifierHandler->getName()] = $modifierHandler;
    }

    public function getModifierHandler(GameModifier $modifier): ?AbstractModifierHandler
    {
        $strategyName = $modifier->getModifierConfig()->getModifierStrategy();

        if (!$strategyName || !isset($this->strategies[$strategyName])) {
            return null;
        }

        return $this->strategies[$strategyName];
    }
}
