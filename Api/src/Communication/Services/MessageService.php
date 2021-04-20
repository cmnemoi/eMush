<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class MessageService implements MessageServiceInterface
{
    const CRAZY_NERON_CHANCE = 25;

    private ChannelServiceInterface $channelService;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomservice;

    public function __construct(
        ChannelServiceInterface $channelService,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomservice
    ) {
        $this->channelService = $channelService;
        $this->entityManager = $entityManager;
        $this->randomservice = $randomservice;
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

    public function createNeronMessage(string $messageCode, Daedalus $daedalus, array $parameters, \DateTime $dateTime): Message
    {
        $publicChannel = $this->channelService->getPublicChannel($daedalus);
        if ($publicChannel === null) {
            throw new \LogicException('Daedalus do not have a public channel');
        }

        $neron = $daedalus->getNeron();
        //Get Neron personality
        if (!$neron->isInhibited()) {
            $parameters['neronMood'] = 'uninhibited';
        } elseif ($this->randomservice->randomPercent() <= self::CRAZY_NERON_CHANCE) {
            $parameters['neronMood'] = 'crazy';
        } else {
            $parameters['neronMood'] = 'neutral';
        }

        $message = new Message();
        $message
            ->setNeron($neron)
            ->setChannel($publicChannel)
            ->setMessage($messageCode)
            ->setTranslationParameters($parameters)
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
