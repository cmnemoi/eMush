<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;

interface ModifierRequirementServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection;

    public function checkRequirements(ModifierActivationRequirementCollection $modifierRequirements, ModifierHolderInterface $holder): bool;
}
