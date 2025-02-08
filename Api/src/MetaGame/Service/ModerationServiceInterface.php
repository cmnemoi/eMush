<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Chat\Entity\Message;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface ModerationServiceInterface
{
    public function banUser(
        User $user,
        User $author,
        string $reason,
        ?string $message = null,
        ?\DateTime $startingDate = null,
        ?\DateInterval $duration = null,
    ): User;

    public function editClosedPlayerMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void;

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void;

    public function deleteMessage(
        Message $message,
        User $author,
        string $reason,
        ?string $adminMessage = null
    ): void;

    public function quarantinePlayer(
        Player $player,
        User $author,
        string $reason,
        ?string $message = null
    ): Player;

    public function warnUser(
        User $user,
        User $author,
        string $reason,
        string $message,
        ?\DateTime $startingDate = null,
        ?\DateInterval $duration = null,
    ): User;

    public function reportPlayer(
        PlayerInfo $player,
        User $author,
        string $reason,
        SanctionEvidenceInterface $sanctionEvidence,
        ?string $message = null
    ): PlayerInfo;

    public function archiveReport(
        ModerationSanction $moderationAction,
        bool $isAbusive
    ): ModerationSanction;

    public function removeSanction(
        ModerationSanction $moderationAction
    ): User;

    public function suspendSanction(
        ModerationSanction $moderationAction
    ): void;
}
