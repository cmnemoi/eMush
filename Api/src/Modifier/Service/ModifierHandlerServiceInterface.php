<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\ModifierHandler\AbstractModifierHandler;

interface ModifierHandlerServiceInterface
{
    public function getModifierHandler(GameModifier $modifier): ?AbstractModifierHandler;
}
