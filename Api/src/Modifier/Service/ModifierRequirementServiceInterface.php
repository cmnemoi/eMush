<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;

interface ModifierRequirementServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers, string $reason, ModifierHolder $holder): ModifierCollection;
}
