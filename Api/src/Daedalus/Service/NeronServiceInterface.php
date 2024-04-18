<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;
use Mush\Player\Entity\Player;

interface NeronServiceInterface
{
    public function changeCpuPriority(Neron $neron, string $cpuPriority, array $reasons = [], ?Player $author = null): void;
}
