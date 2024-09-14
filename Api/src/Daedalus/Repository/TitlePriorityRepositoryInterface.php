<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\TitlePriority;

interface TitlePriorityRepositoryInterface
{
    public function save(TitlePriority $titlePriority): void;
}
