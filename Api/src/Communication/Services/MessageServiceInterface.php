<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

interface MessageServiceInterface
{
    public function getMessageById(int $messageId): ?Message;

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message;

    public function createNeronMessage(string $messageKey, Daedalus $daedalus, array $parameters, \DateTime $dateTime, ?Message $parent = null): Message;

    public function getMessageNeronCycleFailures(Daedalus $daedalus): Message;

    public function getChannelMessages(Player $player, Channel $channel): Collection;
}
