<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Neron;
use Mush\Player\Entity\Player;

interface DepressNeronServiceInterface
{
    public function execute(Neron $neron, ?Player $author = null, array $tags = [], \DateTime $time = new \DateTime()): void;
}
