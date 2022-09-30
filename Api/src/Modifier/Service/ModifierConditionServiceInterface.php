<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;

interface ModifierConditionServiceInterface
{
    public function getActiveModifiers(ModifierCollection $modifiers, string $reason, ModifierHolder $holder): ModifierCollection;
}
