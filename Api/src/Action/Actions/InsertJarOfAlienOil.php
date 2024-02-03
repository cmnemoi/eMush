<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;

final class InsertJarOfAlienOil extends InsertFuel
{
    protected string $name = ActionEnum::INSERT_JAR_OF_ALIEN_OIL;
}
