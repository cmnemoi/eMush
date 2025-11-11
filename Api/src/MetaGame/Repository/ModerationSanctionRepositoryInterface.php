<?php

declare(strict_types=1);

namespace Mush\MetaGame\Repository;

use Mush\MetaGame\Entity\Collection\ModerationSanctionCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface ModerationSanctionRepositoryInterface
{
    public function findAllUserActiveBansAndWarnings(User $user): array;

    public function findUserAllActiveWarnings(User $user): ModerationSanctionCollection;

    public function findUserActiveBan(User $user): ?ModerationSanction;

    public function findAllBansNotYetTriggeredForUser(User $user): ModerationSanctionCollection;

    public function findAllBansNotYetTriggeredForAll(): ModerationSanctionCollection;

    public function findAllPlayerReport(PlayerInfo $player): array;

    public function save(ModerationSanction $moderationSanction): void;
}
