<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\Collection;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;

interface ModifierRequirementServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection;

    public function checkRequirements(Collection $modifierRequirements, ModifierHolderInterface $holder): bool;
}
