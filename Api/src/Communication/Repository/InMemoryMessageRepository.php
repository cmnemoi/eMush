<?php

namespace Mush\Communication\Repository;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;

final class InMemoryMessageRepository implements MessageRepositoryInterface
{
    /** @var array<Message> */
    private array $messages = [];

    public function findNeronCycleReport(Daedalus $daedalus, array $eventTags): ?Message
    {
        foreach ($this->messages as $message) {
            if (
                $message->getNeron() === $daedalus->getDaedalusInfo()->getNeron()->getId()
                && $message->getCreatedAt() >= $daedalus->getCycleStartedAt()
                && $message->getMessage() === NeronMessageEnum::CYCLE_FAILURES
            ) {
                return $message;
            }
        }

        return null;
    }

    public function findByChannel(Channel $channel, ?\DateInterval $ageLimit = null): array
    {
        $messages = [];
        $timeLimit = null;

        if ($ageLimit !== null) {
            $timeLimit = new \DateTime();
            $timeLimit->sub($ageLimit);
        }

        foreach ($this->messages as $message) {
            if (
                $message->getChannel() === $channel
                && $message->getParent() === null
                && ($timeLimit === null || $message->getUpdatedAt() >= $timeLimit)
            ) {
                $messages[] = $message;
            }
        }

        // Sort by updated date desc
        usort($messages, static function ($a, $b) {
            return $b->getUpdatedAt() <=> $a->getUpdatedAt();
        });

        return $messages;
    }

    public function save(Message $message): void
    {
        $id = random_int(1, PHP_INT_MAX);
        (new \ReflectionProperty($message, 'id'))->setValue($message, $id);

        $this->messages[$id] = $message;
    }

    public function saveAll(array $messages): void
    {
        foreach ($messages as $message) {
            $this->save($message);
        }
    }

    public function findById(int $id): ?Message
    {
        return $this->messages[$id] ?? null;
    }

    public function clear(): void
    {
        $this->messages = [];
    }
}
