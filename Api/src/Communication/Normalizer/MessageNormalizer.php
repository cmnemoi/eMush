<?php

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class MessageNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $child = [];

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $language = $currentPlayer->getDaedalus()->getGameConfig()->getLanguage();

        /** @var Message $children */
        foreach ($object->getChild() as $children) {
            $child[] = $this->normalize($children, $format, $context);
        }

        if ($object->getAuthor()) {
            $character = $object->getAuthor()->getCharacterConfig()->getName();
        } else {
            $character = null;
            if ($object->getNeron()) {
                $character = CharacterEnum::NERON;
            }
        }

        $translationParameters = $object->getTranslationParameters();
        if ($this->hasPlayerSymptom($currentPlayer, SymptomEnum::DEAF)) {
            $message = $this->translationService->translate(
                DiseaseMessagesEnum::DEAF,
                [],
                'disease_message',
                $language
            );
        } elseif (
            $object->getAuthor() === $currentPlayer &&
            array_key_exists(DiseaseMessagesEnum::ORIGINAL_MESSAGE, $translationParameters) &&
            $this->hasPlayerSymptom($currentPlayer, $translationParameters[DiseaseMessagesEnum::MODIFICATION_CAUSE])
        ) {
            $message = $translationParameters[DiseaseMessagesEnum::ORIGINAL_MESSAGE];
        } elseif ($object->getAuthor()) {
            $message = $object->getMessage();
        } else {
            $message = $this->translationService->translate(
                $object->getMessage(),
                $translationParameters,
                'neron',
                $language
            );
        }

        // compute message age
        $messageAge = time() - strtotime($object->getCreatedAt()->format('Y-m-d H:i:s'));

        return [
            'id' => $object->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate(
                    "{$character}.name",
                    [],
                    'characters',
                    $language
                ),
            ],
            'message' => $message,
            'age' => $this->translateMessageAge($messageAge, $language),
            'child' => $child,
        ];
    }

    private function hasPlayerSymptom(Player $player, string $symptom): bool
    {
        return $player->getMedicalConditions()->getActiveDiseases()->getAllSymptoms()->hasSymptomByName($symptom);
    }

    private function translateMessageAge(int $messageAge, string $language): string
    {
        if ($messageAge < 60) {
            return $this->translationService->translate(
                'instant',
                [],
                'misc',
                $language
            );
        }

        $minutes = floor($messageAge / 60);
        if ($minutes < 60) {
            return "{$minutes}min";
        }

        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return "~{$hours}h";
        }

        $days = floor($hours / 24);

        return $days . $this->translationService->translate(
            'day',
            [],
            'misc',
            $language
        );
    }
}
