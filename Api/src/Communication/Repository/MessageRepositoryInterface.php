<?php

declare(strict_types=1);

namespace Mush\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;

interface MessageRepositoryInterface
{
    public function findNeronCycleReport(Daedalus $daedalus, array $eventTags): ?Message;

    public function findByChannel(Channel $channel, ?\DateInterval $ageLimit = null): array;

    public function save(Message $message): void;

    /** @param array<Message> $messages */
    public function saveAll(array $messages): void;

    public function findById(int $id): ?Message;
}
