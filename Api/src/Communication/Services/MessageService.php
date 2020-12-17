<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Player\Entity\Player;

class MessageService implements MessageServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
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

        if ($createMessage->getParent() !== null) {
            $root = $createMessage->getParent();
            $rootMessage = $createMessage->getParent();
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
            ->getRepository(Message::class)->findBy(['channel' => $channel, 'parent' => null], ['updatedAt' => 'desc'])
        );
    }

    public function getMessageById(int $messageId): ?Message
    {
        return $this->entityManager->getRepository(Message::class)->find($messageId);
    }
}
