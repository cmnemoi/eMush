<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;

interface RemoveHealthFromPlayerServiceInterface
{
    public function execute(
        int $quantity,
        Player $player,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::HIDDEN
    ): void;
}
