<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Game\Enum\ActionOutputEnum;

// extends success because even though the effect is negative, we don't want to trigger Creative
class MushShowerDamage extends Success
{
    public function getName(): string
    {
        return ActionOutputEnum::MUSH_SHOWER_DAMAGE;
    }
}
