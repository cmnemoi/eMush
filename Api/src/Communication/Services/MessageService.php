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
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Repository\MessageRepository;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

class MessageService implements MessageServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private MessageRepository $messageRepository;
    private ChannelRepository $channelRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        MessageRepository $messageRepository,
        ChannelRepository $channelRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->messageRepository = $messageRepository;
        $this->channelRepository = $channelRepository;
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

    public function getChannelMessages(?Player $player, Channel $channel): Collection
    {
        $ageLimit = null;
        if ($channel->getScope() === ChannelScopeEnum::MUSH) {
            $ageLimit = new \DateInterval('PT24H');
        }

        $messages = new ArrayCollection($this->messageRepository->findByChannel($channel, $ageLimit));

        // if a message has been put in favorite, remove it from the public channel messages for the player
        if ($player) {
            $favoriteChannel = $this->channelRepository->findFavoritesChannelForPlayer($player->getPlayerInfo());
            if ($channel->isPublic() && $favoriteChannel) {
                $this->removeFavoritesMessagesFromPublicChannel($messages, $favoriteChannel, $ageLimit);
            }
        }

        // apply messages modifications
        /** @var ArrayCollection<int, Message> $modifiedMessages */
        $modifiedMessages = new ArrayCollection();
        foreach ($messages as $message) {
            $messageEvent = new MessageEvent(
                $message,
                $player,
                [],
                new \DateTime()
            );
            /** @var MessageEvent $event */
            $event = $this->eventService->computeEventModifications($messageEvent, MessageEvent::READ_MESSAGE);

            $modifiedMessages->add($event->getMessage());
        }

        return $modifiedMessages;
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
        return $this->getChannelMessages($player, $channel)->filter(
            fn (Message $message) => $message->isUnreadBy($player)
        )->count();
    }

    public function markMessageAsReadForPlayer(Message $message, Player $player): void
    {
        $message->addReader($player);

        $message->cancelTimestampable(); // We don't want to update the updatedAt field when player reads the message because this would change the order of the messages
        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    public function putMessageInFavoritesForPlayer(Message $message, Player $player, Channel $favoritesChannel): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();

        $clonedRootMessage = clone $rootMessage;
        $clonedChildren = $clonedRootMessage
            ->getChild()
            ->map(fn (Message $child) => clone $child)
            ->map(fn (Message $child) => $child->setParent($clonedRootMessage))
        ;

        $clonedRootMessage->addFavorite($player);
        $clonedRootMessage->setChannel($favoritesChannel);

        $clonedRootMessage->cancelTimestampable();
        $this->entityManager->persist($clonedRootMessage);

        $clonedChildren->map(fn (Message $child) => $child->cancelTimestampable());
        $clonedChildren->map(fn (Message $child) => $this->entityManager->persist($child));

        $this->entityManager->flush();
    }

    public function removeMessageFromFavoritesForPlayer(Message $message, Player $player): void
    {
        /** @var Message $rootMessage */
        $rootMessage = $message->isRoot() ? $message : $message->getParent();
        $rootMessage->removeFavorite($player);

        $this->entityManager->remove($rootMessage);
        $this->entityManager->flush();
    }

    public function markChannelAsReadForPlayer(Channel $channel, Player $player): void
    {
        $messages = $this->messageRepository->findByChannel($channel);
        foreach ($messages as $message) {
            $message->addReader($player);

            $message->cancelTimestampable();
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();
    }

    private function removeFavoritesMessagesFromPublicChannel(ArrayCollection $messages, Channel $favoriteChannel, ?\DateInterval $ageLimit): void
    {
        $favoritesMesages = $this->messageRepository->findByChannel($favoriteChannel, $ageLimit);

        /** @var Message $message */
        foreach ($messages as $message) {
            /** @var Message $favoriteMessage */
            foreach ($favoritesMesages as $favoriteMessage) {
                /** @var \DateTime $a */
                $a = $message->getCreatedAt();
                /** @var \DateTime $b */
                $b = $favoriteMessage->getCreatedAt();

                $hasBeenPostedAtTheSameTime = date_diff($a, $b)->s === 0;
                if (
                    $hasBeenPostedAtTheSameTime
                    && $message->getMessage() === $favoriteMessage->getMessage()
                    && $message->getTranslationParameters() === $favoriteMessage->getTranslationParameters()
                ) {
                    $messages->removeElement($message);
                }
            }
        }
    }
}
