<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

class MessageService implements MessageServiceInterface
{
    private ChannelServiceInterface $channelService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ChannelServiceInterface $channelService,
        EntityManagerInterface $entityManager
    ) {
        $this->channelService = $channelService;
        $this->entityManager = $entityManager;
    }

    public function createPlayerMessage(Player $player, CreateMessage $createMessage): Message
    {
        $message = new Message();
        $message
            ->setAuthor($player)
            ->setChannel($createMessage->getChannel())
            ->setMessage($createMessage->getMessage())
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

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function getChannelMessages(Player $player, Channel $channel): Collection
    {
        return new ArrayCollection($this->entityManager
            ->getRepository(Message::class)
            ->findBy(['channel' => $channel, 'parent' => null], ['updatedAt' => 'desc']))
            ;
    }

    public function createNeronMessage(string $messageCode, Daedalus $daedalus, \DateTime $dateTime): Message
    {
        $publicChannel = $this->channelService->getPublicChannel($daedalus);
        if ($publicChannel === null) {
            throw new \LogicException('Daedalus do not have a public channel');
        }

        $message = new Message();
        $message
            ->setNeron($daedalus->getNeron())
            ->setChannel($publicChannel)
            ->setMessage($messageCode)
            ->setCreatedAt($dateTime)
            ->setUpdatedAt($dateTime)
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessageById(int $messageId): ?Message
    {
        return $this->entityManager->getRepository(Message::class)->find($messageId);
    }
}
