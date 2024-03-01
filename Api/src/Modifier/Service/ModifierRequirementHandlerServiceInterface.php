<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\ModifierRequirementHandler\AbstractModifierRequirementHandler;

interface ModifierRequirementHandlerServiceInterface
{
    public function getModifierRequirementHandler(ModifierActivationRequirement $requirement): ?AbstractModifierRequirementHandler;
}
