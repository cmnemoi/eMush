<?php

namespace Mush\Communication\Services;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Enum\MessageModificationEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogDeclinationEnum;

final class MessageModifierService implements MessageModifierServiceInterface
{
    public const int PARANOIA_DENIAL = 50;
    private const int COPROLALIA_TRIGGER_CHANCE = 33;
    private const int COPROLALIA_REPLACE_CHANCE = 50;

    private const int PARANOIA_TRIGGER_CHANCE = 33;
    private const int PARANOIA_REPLACE_CHANCE = 60;
    private const int PARANOIA_ACCUSE_CHANCE = 50;

    private RandomServiceInterface $randomService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
    ) {
        $this->randomService = $randomService;
        $this->translationService = $translationService;
    }

    public function applyModifierEffects(
        Message $message,
        ?Player $player,
        string $effectName
    ): Message {
        $messageContent = $message->getMessage();

        return match ($effectName) {
            MessageModificationEnum::COPROLALIA_MESSAGES => $this->applyCoprolaliaEffect($message, $player),
            MessageModificationEnum::PARANOIA_MESSAGES => $this->applyParanoiaEffect($message, $player),
            MessageModificationEnum::PARANOIA_DENIAL => $this->applyParanoiaDenial($message, $player),
            MessageModificationEnum::DEAF_LISTEN => $message->setMessage($this->applyDeafListenEffect()),
            MessageModificationEnum::DEAF_SPEAK => $message->setMessage($this->applyDeafSpeakEffect($messageContent)),
            default => $message,
        };
    }

    private function applyDeafSpeakEffect(string $message): string
    {
        return strtoupper($message);
    }

    private function applyDeafListenEffect(): string
    {
        return '...';
    }

    private function applyCoprolaliaEffect(Message $message, ?Player $player): Message
    {
        if ($player === null) {
            throw new \LogicException('Coprolalia modifier can only be applied to player sent messages');
        }

        $language = $player->getDaedalus()->getLanguage();

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

            $messageContent .= $this->createCoprolaliaMessage($messageKey, $language);
        }

        $message
            ->setMessage($messageContent);

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

    private function applyParanoiaEffect(Message $message, ?Player $player): Message
    {
        if ($player === null) {
            throw new \LogicException('Paranoia modifier can only be applied to player sent messages');
        }

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

        if (!$this->randomService->isSuccessful(self::PARANOIA_DENIAL)) {
            $message
                ->setTranslationParameters([
                    DiseaseMessagesEnum::ORIGINAL_MESSAGE => $message->getMessage(),
                    DiseaseMessagesEnum::MODIFICATION_CAUSE => MessageModificationEnum::PARANOIA_MESSAGES,
                ]);
        }

        $message->setMessage($messageContent);

        return $message;
    }

    private function applyParanoiaDenial(Message $message, ?Player $player): Message
    {
        if ($player === null) {
            throw new \LogicException('Paranoia modifier can only be applied to player sent messages');
        }

        $translationParameters = $message->getTranslationParameters();
        if (
            $message->getAuthor() === $player->getPlayerInfo()
            && \array_key_exists(DiseaseMessagesEnum::ORIGINAL_MESSAGE, $translationParameters)
            && $translationParameters[DiseaseMessagesEnum::MODIFICATION_CAUSE] === MessageModificationEnum::PARANOIA_MESSAGES
        ) {
            $message->setMessage($translationParameters[DiseaseMessagesEnum::ORIGINAL_MESSAGE]);
        }

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

        return reset($characterDraw);
    }

    private function getVersionParameter(array $parameters, string $versionKey): array
    {
        if (\array_key_exists($versionKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$versionKey] as $keyVersion => $versionNb) {
                $parameters[$keyVersion] = $this->randomService->random(1, $versionNb);
            }
        }

        return $parameters;
    }
}
