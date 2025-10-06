<?php

declare(strict_types=1);

namespace Mush\MetaGame\TestDoubles;

use Mush\Chat\Entity\Message;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * Fake implementation of ModerationServiceInterface for testing.
 */
final class FakeModerationService implements ModerationServiceInterface
{
    public array $bannedUsers = [];
    public array $quarantinedPlayers = [];

    public function banUser(
        User $user,
        User $author,
        string $reason,
        ?string $message = null,
        ?\DateTime $startingDate = null,
        ?\DateInterval $duration = null,
    ): User {
        $endDate = null;
        if ($duration !== null) {
            $endDate = (clone ($startingDate ?? new \DateTime()))->add($duration);
        }

        $this->bannedUsers[] = [
            'user' => $user,
            'author' => $author,
            'reason' => $reason,
            'message' => $message,
            'startingDate' => $startingDate,
            'duration' => $endDate,
        ];

        return $user;
    }

    public function quarantinePlayer(
        Player $player,
        User $author,
        string $reason,
        ?string $message = null
    ): Player {
        $this->quarantinedPlayers[] = [
            'player' => $player,
            'author' => $author,
            'reason' => $reason,
            'message' => $message,
        ];

        return $player;
    }

    public function addSanctionEntity(
        User $user,
        ?PlayerInfo $player,
        User $author,
        string $sanctionType,
        string $reason,
        ?\DateTime $startingDate = null,
        ?string $message = null,
        ?\DateInterval $duration = null,
        bool $isVisibleByUser = false,
        ?SanctionEvidenceInterface $sanctionEvidence = null
    ): User {
        return $user;
    }

    public function editClosedPlayerMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void {}

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void {}

    public function removeSanction(ModerationSanction $moderationAction): User
    {
        return new User();
    }

    public function suspendSanction(ModerationSanction $moderationAction): void {}

    public function deleteMessage(
        Message $message,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void {}

    public function warnUser(
        User $user,
        User $author,
        string $reason,
        string $message,
        ?\DateTime $startingDate = null,
        ?\DateInterval $duration = null,
    ): User {
        return $user;
    }

    public function reportPlayer(
        PlayerInfo $player,
        User $author,
        string $reason,
        SanctionEvidenceInterface $sanctionEvidence,
        ?string $message = null,
    ): PlayerInfo {
        return $player;
    }

    public function archiveReport(
        ModerationSanction $moderationAction,
        bool $isAbusive
    ): ModerationSanction {
        return $moderationAction;
    }
}
