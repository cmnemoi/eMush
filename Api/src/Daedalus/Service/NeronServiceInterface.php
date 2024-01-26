<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;

interface NeronServiceInterface
{
    public function changeCpuPriority(Neron $neron, string $cpuPriority, array $reasons): void;
}
