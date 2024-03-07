<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;

interface ModifierRequirementServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection;

    public function checkModifier(AbstractModifierConfig $modifierConfig, ModifierHolderInterface $holder): bool;
}
