<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;

final class DaedalusFactory
{
    public static function createDaedalus(): Daedalus
    {
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());

        return $daedalus;
    }
}
