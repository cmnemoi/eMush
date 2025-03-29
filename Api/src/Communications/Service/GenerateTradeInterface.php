<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\Trade;
use Mush\Hunter\Entity\Hunter;

interface GenerateTradeInterface
{
    public function execute(Hunter $transport): Trade;
}
