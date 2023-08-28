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
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;

class MessageService implements MessageServiceInterface
{
    private EntityManagerInterface $entityManager;
    private DiseaseMessageServiceInterface $diseaseMessageService;
    private EventServiceInterface $eventService;
    private MessageRepository $messageRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseMessageServiceInterface $diseaseMessageService,
        EventServiceInterface $eventService,
        MessageRepository $messageRepository
    ) {
        $this->entityManager = $entityManager;
        $this->diseaseMessageService = $diseaseMessageService;
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

        $message = $this->diseaseMessageService->applyDiseaseEffects($message);

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

        $messageEvent = new MessageEvent($message, [], new \DateTime());
        $this->eventService->callEvent($messageEvent, MessageEvent::NEW_MESSAGE);

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

        return new ArrayCollection($this->messageRepository->findByChannel($channel, $ageLimit));
    }

    public function canPlayerPostMessage(Player $player, Channel $channel): bool
    {
        if ($player->hasStatus(PlayerStatusEnum::GAGGED)
            || $player->getMedicalConditions()->getActiveDiseases()->getAllSymptoms()->hasSymptomByName(SymptomEnum::MUTE)
            || !$player->isAlive()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMessageById(int $messageId): ?Message
    {
        return $this->entityManager->getRepository(Message::class)->find($messageId);
    }
}
