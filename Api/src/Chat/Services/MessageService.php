<?php

declare(strict_types=1);

namespace Mush\Chat\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Event\MessageEvent;
use Mush\Chat\Repository\MessageRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

final class MessageService implements MessageServiceInterface
{
    public const string MUTE_PLAYER_SPEAKING_IN_MUSH_CHANNEL = 'mute_player_speaking_in_mush_channel';

    public function __construct(
        private EventServiceInterface $eventService,
        private MessageRepositoryInterface $messageRepository,
    ) {}

    public function save(Message $message): void
    {
        $this->messageRepository->save($message);
    }

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message
    {
        $messageContent = trim($createMessage->getMessage());
        $channel = $createMessage->getChannel();

        $message = $this->initializeMessage($player, $createMessage);

        if ($createMessage->isVocodedAnnouncement()) {
            $this->handleVocodedAnnouncement($message, $messageContent, $player);
        } else {
            $message->setMessage($messageContent);
        }

        $this->updateRootMessageTimestamp($createMessage->getParent());

        $message = $this->modifyMessage($message, $player, $channel);

        $this->messageRepository->save($message);

        return $message;
    }

    public function createSystemMessage(
        string $messageKey,
        Channel $channel,
        array $parameters,
        \DateTime $dateTime,
    ): Message {
        $daedalusInfo = $channel->getDaedalusInfo();
        $daedalus = $daedalusInfo->getDaedalus();
        if ($daedalus) {
            $day = $daedalus->getDay();
            $cycle = $daedalus->getCycle();
        } else {
            $day = $daedalusInfo->getClosedDaedalus()->getEndDay();
            $cycle = $daedalusInfo->getClosedDaedalus()->getEndCycle();
        }

        $message = new Message();
        $message
            ->setChannel($channel)
            ->setMessage($messageKey)
            ->setTranslationParameters($parameters)
            ->setCreatedAt($dateTime)
            ->setUpdatedAt($dateTime)
            ->setCycle($cycle)
            ->setDay($day);

        $channel->addMessage($message);

        $this->messageRepository->save($message);

        return $message;
    }

    public function getChannelMessages(?Player $player, Channel $channel, \DateInterval $timeLimit): Collection
    {
        $timeLimit = $channel->isMushChannel() ? new \DateInterval('PT24H') : $timeLimit;

        $messages = $this->getByChannelWithTimeLimit($channel, $timeLimit);

        if ($player && $channel->isPublic()) {
            $messages = $messages->filter(static fn (Message $message) => !$message->isFavoriteFor($player));
        }

        if (!$player) {
            return $messages;
        }

        return $messages->map(fn (Message $message) => $this->getModifiedMessage($message, $player));
    }

    public function getPlayerFavoritesChannelMessages(Player $player): Collection
    {
        return $player->getFavoriteMessages()->map(fn (Message $message) => $this->getModifiedMessage($message, $player));
    }

    public function canPlayerPostMessage(Player $player, Channel $channel): bool
    {
        if (!$player->isAlive()) {
            return false;
        }

        if ($channel->isMushChannel() && $player->isMush()) {
            return true;
        }

        $dummyMessage = new Message();
        $dummyMessage->setMessage('')->setAuthor($player->getPlayerInfo())->setChannel($channel);
        $messageEvent = new MessageEvent(
            $dummyMessage,
            $player,
            [],
            new \DateTime()
        );
        $event = $this->eventService->computeEventModifications($messageEvent, MessageEvent::NEW_MESSAGE);

        return $event !== null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessageById(int $messageId): ?Message
    {
        return $this->messageRepository->findById($messageId);
    }

    public function getNumberOfNewMessagesForPlayer(Player $player, Channel $channel): int
    {
        $messages = $channel->isFavorites() ? $player->getFavoriteMessages() : $this->getChannelMessages($player, $channel, timeLimit: new \DateInterval('PT48H'));

        $nbNewMessages = 0;
        foreach ($messages as $message) {
            if ($message->isUnreadBy($player)) {
                ++$nbNewMessages;
            }
            foreach ($message->getChild() as $child) {
                if ($child->isUnreadBy($player)) {
                    ++$nbNewMessages;
                }
            }
        }

        return $nbNewMessages;
    }

    public function markMessageAsReadForPlayer(Message $message, Player $player): void
    {
        if ($message->isReadBy($player)) {
            return;
        }

        try {
            $message->addReader($player)->cancelTimestampable();
            $this->messageRepository->save($message);
        } catch (UniqueConstraintViolationException $e) {
            // Ignore as this is probably due to a race condition
        }
    }

    public function putMessageInFavoritesForPlayer(Message $message, Player $player): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();

        $rootMessage
            ->addFavorite($player)
            ->cancelTimestampable();

        $this->messageRepository->save($rootMessage);
    }

    public function removeMessageFromFavoritesForPlayer(Message $message, Player $player): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();

        $rootMessage
            ->removeFavorite($player)
            ->cancelTimestampable();

        $this->messageRepository->save($rootMessage);
    }

    private function initializeMessage(Player $player, CreateMessage $createMessage): Message
    {
        $message = new Message();
        $message
            ->setAuthor($player->getPlayerInfo())
            ->setChannel($createMessage->getChannel())
            ->setParent($createMessage->getParent())
            ->addReader($player)
            ->setCycle($player->getDaedalus()->getCycle())
            ->setDay($player->getDaedalus()->getDay());

        return $message;
    }

    private function handleVocodedAnnouncement(Message $message, string $messageContent, Player $player): void
    {
        $neron = $player->getDaedalus()->getNeron();
        if ($neron->shouldRefuseVocodedAnnouncementsForPlayer($player)) {
            $message->setMessage(NeronMessageEnum::COMMAND_REFUSED);
        } else {
            $messageContent = trim(str_replace('/neron', '', $messageContent));
            $message->setMessage($messageContent);
            $message->setNeron($neron);
        }
    }

    private function updateRootMessageTimestamp(?Message $parentMessage): void
    {
        if (!$parentMessage) {
            return;
        }

        $root = $parentMessage;
        while ($parentMessage = $parentMessage->getParent()) {
            $root = $parentMessage;
        }

        $root->setUpdatedAt(new \DateTime());
        $this->messageRepository->save($root);
    }

    private function modifyMessage(Message $message, Player $player, Channel $channel): Message
    {
        $tags = [];
        if ($player->isMute() && $channel->isMushChannel()) {
            $tags = [self::MUTE_PLAYER_SPEAKING_IN_MUSH_CHANNEL];
        }

        $messageEvent = new MessageEvent(
            $message,
            $player,
            $tags,
            new \DateTime()
        );
        $events = $this->eventService->callEvent($messageEvent, MessageEvent::NEW_MESSAGE);

        /** @var MessageEvent $modifiedEvent */
        $modifiedEvent = $events->getInitialEvent();

        return $modifiedEvent->getMessage();
    }

    private function getByChannelWithTimeLimit(Channel $channel, \DateInterval $timeLimit): Collection
    {
        return new ArrayCollection($this->messageRepository->findByChannel($channel, $timeLimit));
    }

    private function getModifiedMessage(Message $message, Player $player): Message
    {
        $messageEvent = new MessageEvent(
            $message,
            $player,
            [],
            new \DateTime()
        );

        /** @var MessageEvent $event */
        $event = $this->eventService->computeEventModifications($messageEvent, MessageEvent::READ_MESSAGE);

        return $event->getMessage();
    }
}
