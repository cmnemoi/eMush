<?php

namespace Mush\Modifier\ModifierRequirementHandler;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;

abstract class AbstractModifierRequirementHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function checkRequirement(
        ModifierActivationRequirement $modifierRequirement,
        ModifierHolderInterface $holder
    ): bool;
}
