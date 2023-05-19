<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierCollection;

interface ModifierRequirementServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection;
}
