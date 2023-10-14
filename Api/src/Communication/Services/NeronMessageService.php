<?php

namespace Mush\Communication\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Enum\NeronPersonalitiesEnum;
use Mush\Communication\Repository\MessageRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;

class NeronMessageService implements NeronMessageServiceInterface
{
    public const CRAZY_NERON_CHANCE = 25;

    private ChannelServiceInterface $channelService;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private MessageRepository $messageRepository;
    private TranslationServiceInterface $translationService;

    public function __construct(
        ChannelServiceInterface $channelService,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        MessageRepository $messageRepository,
        TranslationServiceInterface $translationService
    ) {
        $this->channelService = $channelService;
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->messageRepository = $messageRepository;
        $this->translationService = $translationService;
    }

    public function createNeronMessage(
        string $messageKey,
        Daedalus $daedalus,
        array $parameters,
        \DateTime $dateTime,
        Message $parent = null
    ): Message {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $publicChannel = $this->channelService->getPublicChannel($daedalusInfo);
        if ($publicChannel === null) {
            throw new \LogicException('Daedalus do not have a public channel');
        }

        $neron = $daedalusInfo->getNeron();
        // Get Neron personality
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
            ->setParent($parent)
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    public function getMessageNeronCycleFailures(Daedalus $daedalus, \DateTime $time): Message
    {
        $message = $this->messageRepository->findNeronCycleReport($daedalus);
        if (!$message) {
            $message = $this->createNeronMessage(NeronMessageEnum::CYCLE_FAILURES, $daedalus, [], $time);
        }

        return $message;
    }

    public function createPlayerDeathMessage(Player $player, string $cause, \DateTime $time): void
    {
        $playerName = $player->getName();

        switch ($playerName) {
            case CharacterEnum::RALUCA:
                $message = NeronMessageEnum::RALUCA_DEATH;
                break;
            case CharacterEnum::JANICE:
                $message = NeronMessageEnum::JANICE_DEATH;
                break;
            default:
                if ($cause === EndCauseEnum::ASPHYXIA) {
                    $message = NeronMessageEnum::ASPHYXIA_DEATH;
                } else {
                    $message = NeronMessageEnum::PLAYER_DEATH;
                }
                break;
        }

        $cause = $this->translationService->translate(
            $cause . '.name',
            [],
            'end_cause',
            $player->getDaedalus()->getLanguage()
        );
        $parameters = ['character' => $playerName, 'cause' => $cause];
        $this->createNeronMessage($message, $player->getDaedalus(), $parameters, $time);
    }

    public function createBrokenEquipmentMessage(GameEquipment $equipment, string $visibility, \DateTime $time): void
    {
        $equipmentName = $equipment->getName();

        $daedalus = $equipment->getDaedalus();

        switch ($equipmentName) {
            case EquipmentEnum::OXYGEN_TANK:
                $message = NeronMessageEnum::BROKEN_OXYGEN;
                break;
            case EquipmentEnum::FUEL_TANK:
                $message = NeronMessageEnum::BROKEN_FUEL;
                break;
            default:
                $message = NeronMessageEnum::BROKEN_EQUIPMENT;
                break;
        }

        $parentMessage = $this->getMessageNeronCycleFailures($daedalus, $time);

        if ($equipment instanceof GameItem) {
            $this->createNeronMessage($message, $daedalus, ['target_item' => $equipmentName], $time, $parentMessage);
        } elseif (!($equipment instanceof Door)) {
            $this->createNeronMessage($message, $daedalus, ['target_equipment' => $equipmentName], $time, $parentMessage);
        }
    }

    public function createNewFireMessage(Daedalus $daedalus, \DateTime $time): void
    {
        $parentMessage = $this->getMessageNeronCycleFailures($daedalus, $time);

        $this->createNeronMessage(NeronMessageEnum::NEW_FIRE, $daedalus, ['quantity' => 1], $time, $parentMessage);
    }

    public function createTitleAttributionMessage(Player $player, string $title, \DateTime $time): void
    {
        $title = $this->translationService->translate(
            $title,
            [],
            'misc',
            $player->getDaedalus()->getLanguage()
        );
        $parameters = ['character' => $player->getName(), 'title' => $title];
        $this->createNeronMessage(NeronMessageEnum::TITLE_ATTRIBUTION, $player->getDaedalus(), $parameters, $time);
    }
}
