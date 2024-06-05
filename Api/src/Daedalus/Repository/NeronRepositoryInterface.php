<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\Neron;

interface NeronRepositoryInterface
{
    public function save(Neron $neron): void;
}
