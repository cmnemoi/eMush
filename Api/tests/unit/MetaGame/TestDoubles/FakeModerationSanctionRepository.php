<?php

declare(strict_types=1);

namespace Mush\Tests\Unit\MetaGame\TestDoubles;

use Mush\MetaGame\Entity\Collection\ModerationSanctionCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Repository\ModerationSanctionRepositoryInterface;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

final class FakeModerationSanctionRepository implements ModerationSanctionRepositoryInterface
{
    private ModerationSanctionCollection $repository;

    public function __construct()
    {
        $this->repository = new ModerationSanctionCollection();
    }

    public function findAllUserActiveBansAndWarnings(User $user): array
    {
        return $this->repository->toArray();
    }

    public function findUserAllActiveWarnings(User $user): ModerationSanctionCollection
    {
        return $this->repository->filter(static fn (ModerationSanction $moderationSanction) => $moderationSanction->getUser()->getId() === $user->getId()
            && $moderationSanction->getModerationAction() === ModerationSanctionEnum::WARNING
            && $moderationSanction->getIsActive() === true);
    }

    public function findUserActiveBan(User $user): ?ModerationSanction
    {
        $bans = $this->repository->filter(static fn (ModerationSanction $moderationAction) => (
            $moderationAction->getModerationAction() === ModerationSanctionEnum::BAN_USER
            && $moderationAction->getIsActive() === true
        ));

        if ($bans->count() === 0) {
            return null;
        }

        // get the last ban given
        $activeBan = $bans->first();
        foreach ($bans as $ban) {
            if ($ban->getStartDate() > $activeBan->getStartDate()) {
                $activeBan = $ban;
            }
        }

        return $activeBan;
    }

    public function findAllBansNotYetTriggeredForUser(User $user): ModerationSanctionCollection
    {
        return $this->repository->filter(static fn (ModerationSanction $moderationSanction) => $moderationSanction->getUser()->getId() === $user->getId()
            && $moderationSanction->getModerationAction() === ModerationSanctionEnum::BAN_USER_PENDING);
    }

    public function findAllBansNotYetTriggeredForAll(): ModerationSanctionCollection
    {
        return $this->repository->filter(static fn (ModerationSanction $moderationSanction) => $moderationSanction->getModerationAction() === ModerationSanctionEnum::BAN_USER_PENDING);
    }

    public function findAllPlayerReport(PlayerInfo $player): array
    {
        return $this->repository->filter(static fn (ModerationSanction $moderationSanction) => $moderationSanction->getPlayerId() === $player->getId()
            && $moderationSanction->getIsReport() === true)->toArray();
    }

    public function save(ModerationSanction $moderationSanction): void
    {
        $this->repository->add($moderationSanction);
    }
}
