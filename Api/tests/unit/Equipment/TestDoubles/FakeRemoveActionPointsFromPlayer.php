<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\TestDoubles;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveActionPointsFromPlayerServiceInterface;

final class FakeRemoveActionPointsFromPlayer implements RemoveActionPointsFromPlayerServiceInterface
{
    public function execute(int $quantity, Player $player, array $tags = [], ?Player $author = null, \DateTime $time = new \DateTime(), string $visibility = VisibilityEnum::HIDDEN): void
    {
        $player->setActionPoint($player->getActionPoint() - $quantity);
    }
}
