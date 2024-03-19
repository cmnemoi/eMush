<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\Communication\Entity\Message;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

interface ModerationServiceInterface
{
    public function banUser(User $user): User;

    public function editClosedPlayerMessage(ClosedPlayer $closedPlayer): void;

    public function hideClosedPlayerEndMessage(ClosedPlayer $closedPlayer): void;

    public function unbanUser(User $user): User;

    public function quarantinePlayer(Player $player): Player;

    public function deleteMessage(Message $message): void;
}
