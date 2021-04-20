<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Enum\NeronPersonalitiesEnum;
use Mush\Communication\Repository\MessageRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogDeclinationEnum;

class MessageService implements MessageServiceInterface
{
    const CRAZY_NERON_CHANCE = 25;

    private ChannelServiceInterface $channelService;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private MessageRepository $messageRepository;

    public function __construct(
        ChannelServiceInterface $channelService,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        MessageRepository $messageRepository
    ) {
        $this->channelService = $channelService;
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->messageRepository = $messageRepository;
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

    public function createNeronMessage(
        string $messageKey,
        Daedalus $daedalus,
        array $parameters,
        \DateTime $dateTime,
        ?Message $parent = null
    ): Message {
        $publicChannel = $this->channelService->getPublicChannel($daedalus);
        if ($publicChannel === null) {
            throw new \LogicException('Daedalus do not have a public channel');
        }

        $neron = $daedalus->getNeron();
        //Get Neron personality
        if (!$neron->isInhibited()) {
            $parameters['neronMood'] = NeronPersonalitiesEnum::UNINHIBITED;
        } elseif ($this->randomService->randomPercent() <= self::CRAZY_NERON_CHANCE) {
            $parameters['neronMood'] = NeronPersonalitiesEnum::CRAZY;
        } else {
            $parameters['neronMood'] = NeronPersonalitiesEnum::NEUTRAL;
        }

        if (array_key_exists($messageKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$messageKey] as $keyVersion => $versionNb) {
                $parameters[$keyVersion] = $this->randomService->random(1, $versionNb);
            }
        }

        $message = new Message();
        $message
            ->setNeron($neron)
            ->setChannel($publicChannel)
            ->setMessage($messageKey)
            ->setTranslationParameters($parameters)
            ->setCreatedAt($dateTime)
            ->setUpdatedAt($dateTime)
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function getMessageNeronCycleFailures(Daedalus $daedalus): Message
    {
        $message = $this->messageRepository->findNeronCycleReport($daedalus);

        if (!$message) {
            $message = $this->createNeronMessage(NeronMessageEnum::CYCLE_FAILURES, $daedalus, [], new \DateTime());
        }

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
