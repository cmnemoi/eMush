<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Communication\Entity\Message;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

interface ModerationServiceInterface
{
    public function banUser(
        User $user,
        User $author,
        ?\DateInterval $duration,
        string $reason,
        ?string $message,
        ?\DateTime $startingDate = null
    ): User;

    public function editClosedPlayerMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage
    ): void;

    public function hideClosedPlayerEndMessage(
        ClosedPlayer $closedPlayer,
        User $author,
        string $reason,
        ?string $adminMessage
    ): void;

    public function deleteMessage(
        Message $message,
        User $author,
        string $reason,
        ?string $adminMessage
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
        ?\DateInterval $duration,
        string $reason,
        string $message,
        ?\DateTime $startingDate = null
    ): User;

    public function reportPlayer(
        PlayerInfo $player,
        User $author,
        string $reason,
        ?string $message,
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
