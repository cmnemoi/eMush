<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusTravelServiceInterface
{
    public function turnDaedalusLeft(Daedalus $daedalus, array $reasons): Daedalus;

    public function turnDaedalusRight(Daedalus $daedalus, array $reasons): Daedalus;
}
