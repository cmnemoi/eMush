<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Player\Entity\Player;

interface MessageServiceInterface
{
    public function getMessageById(int $messageId): ?Message;

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message;

    public function createSystemMessage(
        string $messageKey,
        Channel $channel,
        array $parameters,
        \DateTime $dateTime,
    ): Message;

    public function getChannelMessages(?Player $player, Channel $channel, int $page, int $limit): Collection;

    public function canPlayerPostMessage(Player $player, Channel $channel): bool;
}
