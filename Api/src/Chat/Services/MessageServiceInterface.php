<?php

namespace Mush\Chat\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Player\Entity\Player;

interface MessageServiceInterface
{
    public function save(Message $message): void;

    public function getMessageById(int $messageId): ?Message;

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message;

    public function createSystemMessage(
        string $messageKey,
        Channel $channel,
        array $parameters,
        \DateTime $dateTime,
    ): Message;

    public function getChannelMessages(?Player $player, Channel $channel, \DateInterval $timeLimit): Collection;

    public function getPlayerFavoritesChannelMessages(Player $player): Collection;

    public function canPlayerPostMessage(Player $player, Channel $channel): bool;

    public function getNumberOfNewMessagesForPlayer(Player $player, Channel $channel): int;

    public function markMessageAsReadForPlayer(Message $message, Player $player): void;

    public function putMessageInFavoritesForPlayer(Message $message, Player $player): void;

    public function removeMessageFromFavoritesForPlayer(Message $message, Player $player): void;
}
