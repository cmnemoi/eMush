<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\MetaGame\Entity\Skin\Skin;
use Mush\MetaGame\Entity\Skin\SkinableEntityInterface;

interface SkinServiceInterface
{
    public function applySkinToAllDaedalus(Skin $skin, Daedalus $daedalus): void;

    public function applySkinToEntity(SkinableEntityInterface $skinableEntity, Skin $skin): void;
}
