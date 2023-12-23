<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\HunterCollection;

interface HunterNormalizerHelperInterface
{
    public function getHuntersToNormalize(Daedalus $daedalus): HunterCollection;
}
