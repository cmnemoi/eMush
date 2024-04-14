<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\MessageEvent;
use Mush\Communication\Repository\MessageRepository;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

class MessageService implements MessageServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private MessageRepository $messageRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        MessageRepository $messageRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->messageRepository = $messageRepository;
    }

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message
    {
        $messageContent = trim($createMessage->getMessage());

        $message = new Message();
        $message
            ->setAuthor($player->getPlayerInfo())
            ->setChannel($createMessage->getChannel())
            ->setMessage($messageContent)
            ->setParent($createMessage->getParent())
            ->addReader($player)
        ;

        $rootMessage = $createMessage->getParent();
        if ($rootMessage) {
            $root = $rootMessage;
            while ($rootMessage = $rootMessage->getParent()) {
                $root = $rootMessage;
            }

            $root->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($root);
        }

        $messageEvent = new MessageEvent(
            $message,
            $player,
            [],
            new \DateTime()
        );
        $events = $this->eventService->callEvent($messageEvent, MessageEvent::NEW_MESSAGE);

        /** @var MessageEvent $modifiedEvent */
        $modifiedEvent = $events->getInitialEvent();
        $message = $modifiedEvent->getMessage();

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function createSystemMessage(
        string $messageKey,
        Channel $channel,
        array $parameters,
        \DateTime $dateTime,
    ): Message {
        $message = new Message();
        $message
            ->setChannel($channel)
            ->setMessage($messageKey)
            ->setTranslationParameters($parameters)
            ->setCreatedAt($dateTime)
            ->setUpdatedAt($dateTime)
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function getChannelMessages(?Player $player, Channel $channel, int $page, int $limit): Collection
    {
        if ($channel->getScope() === ChannelScopeEnum::MUSH) {
            $messages = $this->getByChannelWithTimeLimit($channel, new \DateInterval('PT24H'));
        } else {
            $messages = $this->getByChannelWithPagination($channel, $page, $limit);
            // if a message has been put in favorite, remove it from the public channel messages for the player
            if ($player && $channel->isPublic()) {
                $messages = $messages->filter(static fn (Message $message) => !$message->isFavoriteFor($player));
            }
        }

        if (!$player) {
            return $messages;
        }

        return $messages->map(fn (Message $message) => $this->getModifiedMessage($message, $player));
    }

    public function getPlayerFavoritesChannelMessages(Player $player, int $page, int $limit): Collection
    {
        $messages = new ArrayCollection($player->getFavoriteMessages()->slice(($page - 1) * $limit, $limit));

        return $messages->map(fn (Message $message) => $this->getModifiedMessage($message, $player));
    }

    public function canPlayerPostMessage(Player $player, Channel $channel): bool
    {
        if (!$player->isAlive()) {
            return false;
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
        return $this->entityManager->getRepository(Message::class)->find($messageId);
    }

    public function getNumberOfNewMessagesForPlayer(Player $player, Channel $channel): int
    {
        $messages = $channel->isFavorites() ? $player->getFavoriteMessages() : $this->getChannelMessages($player, $channel, page: 1, limit: 20);

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
        $message
          ->addReader($player)
          ->cancelTimestampable(); // We don't want to update the updatedAt field when player reads the message because this would change the order of the messages

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    public function putMessageInFavoritesForPlayer(Message $message, Player $player): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();

        $rootMessage->addFavorite($player);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    public function removeMessageFromFavoritesForPlayer(Message $message, Player $player): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();

        $rootMessage->removeFavorite($player);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    private function getByChannelWithTimeLimit(Channel $channel, \DateInterval $timeLimit): Collection
    {
        return new ArrayCollection($this->messageRepository->findByChannel($channel, $timeLimit));
    }

    private function getByChannelWithPagination(Channel $channel, int $page, int $limit): Collection
    {
        return new ArrayCollection($this->messageRepository->findByChannelWithPagination($channel, $page, $limit));
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
