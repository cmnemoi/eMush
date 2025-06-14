<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\DaedalusInfo;

interface FunFactsServiceInterface
{
    public function generateForDaedalusInfo(DaedalusInfo $daedalusInfo): void;
}
