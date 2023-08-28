<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogDeclinationEnum;

class DiseaseMessageService implements DiseaseMessageServiceInterface
{
    private const COPROLALIA_TRIGGER_CHANCE = 33;
    private const COPROLALIA_REPLACE_CHANCE = 50;

    private const PARANOIA_TRIGGER_CHANCE = 33;
    private const PARANOIA_REPLACE_CHANCE = 60;
    private const PARANOIA_ACCUSE_CHANCE = 50;
    public const PARANOIA_AWARENESS = 50;

    private RandomServiceInterface $randomService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
    ) {
        $this->randomService = $randomService;
        $this->translationService = $translationService;
    }

    public function applyDiseaseEffects(Message $message): Message
    {
        $messageContent = $message->getMessage();

        $playerInfo = $message->getAuthor();

        if ($playerInfo === null
            || ($player = $playerInfo->getPlayer()) === null
        ) {
            return $message;
        }

        $playerSymptoms = $player
            ->getMedicalConditions()
            ->getActiveDiseases()
            ->getAllSymptoms()
            ->getTriggeredSymptoms([EventEnum::NEW_MESSAGE])
        ;

        if ($playerSymptoms->hasSymptomByName(SymptomEnum::COPROLALIA_MESSAGES)) {
            $message = $this->applyCoprolaliaEffect($message, $player->getDaedalus()->getLanguage());
        }

        if ($playerSymptoms->hasSymptomByName(SymptomEnum::PARANOIA_MESSAGES)) {
            $message = $this->applyParanoiaEffect($message, $player);
        }

        if ($playerSymptoms->hasSymptomByName(SymptomEnum::DEAF)) {
            $message->setMessage($this->applyDeafEffect($messageContent));
        }

        return $message;
    }

    private function applyDeafEffect(string $message): string
    {
        return strtoupper($message);
    }

    private function applyCoprolaliaEffect(Message $message, string $language): Message
    {
        $messageContent = $message->getMessage();

        if (!$this->randomService->isSuccessful(self::COPROLALIA_TRIGGER_CHANCE)) {
            return $message;
        }

        if ($this->randomService->isSuccessful(self::COPROLALIA_REPLACE_CHANCE)) {
            $messageKey = DiseaseMessagesEnum::REPLACE_COPROLALIA;

            $messageContent = $this->createCoprolaliaMessage($messageKey, $language);
        } elseif ($this->randomService->isSuccessful(50)) {
            $messageKey = DiseaseMessagesEnum::PRE_COPROLALIA;

            $messageContent = lcfirst($messageContent);

            $messageContent = $this->createCoprolaliaMessage($messageKey, $language) . $messageContent;
        } else {
            $messageKey = DiseaseMessagesEnum::POST_COPROLALIA;

            $messageContent = rtrim($messageContent, '.?!');

            $messageContent = $messageContent . $this->createCoprolaliaMessage($messageKey, $language);
        }

        $message
            ->setMessage($messageContent)
        ;

        return $message;
    }

    private function createCoprolaliaMessage(string $messageKey, string $language): string
    {
        $parameters = [];

        // get message version
        $parameters = $this->getVersionParameter($parameters, $messageKey);

        return $this->translationService->translate(
            $messageKey,
            $parameters,
            'disease_message',
            $language
        );
    }

    private function applyParanoiaEffect(Message $message, Player $player): Message
    {
        if (!$this->randomService->isSuccessful(self::PARANOIA_TRIGGER_CHANCE)) {
            return $message;
        }

        $messageContent = $message->getMessage();

        $language = $player->getDaedalus()->getLanguage();

        $parameters = [];
        if ($this->randomService->isSuccessful(self::PARANOIA_REPLACE_CHANCE)) {
            if ($this->randomService->isSuccessful(self::PARANOIA_ACCUSE_CHANCE)) {
                $messageKey = DiseaseMessagesEnum::ACCUSE_PARANOIA;

                $parameters['character'] = $this->getRandomOtherPlayer($player);
            } else {
                $messageKey = DiseaseMessagesEnum::REPLACE_PARANOIA;
            }

            $parameters = $this->getVersionParameter($parameters, $messageKey);

            $messageContent = $this->translationService->translate(
                $messageKey,
                $parameters,
                'disease_message',
                $language
            );
        } else {
            $messageKey = DiseaseMessagesEnum::PRE_PARANOIA;

            $parameters = $this->getVersionParameter($parameters, $messageKey);

            $messageContent = lcfirst($messageContent);

            $messageContent = $this->translationService->translate(
                $messageKey,
                $parameters,
                'disease_message',
                $language
            ) . $messageContent;
        }

        if (!$this->randomService->isSuccessful(self::PARANOIA_AWARENESS)) {
            $message
                ->setTranslationParameters([
                    DiseaseMessagesEnum::ORIGINAL_MESSAGE => $message->getMessage(),
                    DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
                ]);
        }

        $message->setMessage($messageContent);

        return $message;
    }

    private function getRandomOtherPlayer(Player $player): string
    {
        $characterConfigs = $player->getDaedalus()->getGameConfig()->getCharactersConfig();

        $characters = [];
        /** @var CharacterConfig $characterConfig */
        foreach ($characterConfigs as $characterConfig) {
            $characterName = $characterConfig->getCharacterName();
            if ($characterName !== $player->getName()) {
                $characters[] = $characterConfig->getCharacterName();
            }
        }

        $characterDraw = $this->randomService->getRandomElements($characters, 1);
        $character = reset($characterDraw);

        return $character;
    }

    private function getVersionParameter(array $parameters, string $versionKey): array
    {
        if (array_key_exists($versionKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$versionKey] as $keyVersion => $versionNb) {
                $parameters[$keyVersion] = $this->randomService->random(1, $versionNb);
            }
        }

        return $parameters;
    }
}
