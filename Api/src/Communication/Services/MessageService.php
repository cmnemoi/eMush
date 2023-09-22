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
        MessageRepository $messageRepository
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
        $message = $messageEvent->getMessage();

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

    public function getChannelMessages(Player $player, Channel $channel): Collection
    {
        $ageLimit = null;
        if ($channel->getScope() === ChannelScopeEnum::MUSH) {
            $ageLimit = new \DateInterval('PT24H');
        }

        $messages = $this->messageRepository->findByChannel($channel, $ageLimit);
        $modifiedMessages = [];

        foreach ($messages as $message) {
            $messageEvent = new MessageEvent(
                $message,
                $player,
                [],
                new \DateTime()
            );
            /** @var MessageEvent $event */
            $event = $this->eventService->computeEventModifications($messageEvent, MessageEvent::READ_MESSAGE);

            $modifiedMessages[] = $event->getMessage();
        }

        return new ArrayCollection($modifiedMessages);
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
}
