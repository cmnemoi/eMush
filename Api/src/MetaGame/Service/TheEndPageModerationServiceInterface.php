<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Player\Entity\ClosedPlayer;

interface TheEndPageModerationServiceInterface
{
    public function editEndMessage(ClosedPlayer $closedPlayer): void;
}
