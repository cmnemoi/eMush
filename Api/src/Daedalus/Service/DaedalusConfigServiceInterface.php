<?php


namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\DaedalusConfig;

interface DaedalusConfigServiceInterface
{
    public function getConfig(): DaedalusConfig;
}
