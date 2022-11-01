<?php

namespace Mush\RoomLog\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class RoomLogNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof RoomLog;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $currentPlayer = $context['currentPlayer'];
        $language = $currentPlayer->getDaedalus()->getGameConfig()->getLanguage();

        // compute log age
        $logAge = time() - strtotime($object->getDate()->format('Y-m-d H:i:s'));

        return [
            'log' => $this->translationService->translate(
                $object->getLog(),
                $object->getParameters(),
                $object->getType(),
                $language
            ),
            'visibility' => $object->getVisibility(),
            'age' => $this->translateLogAge($logAge, $language),
        ];
    }

    private function translateLogAge(int $logAge, string $language): string
    {
        if ($logAge < 60) {
            return $this->translationService->translate(
                'instant',
                [],
                'misc',
                $language
            );
        }

        $minutes = floor($logAge / 60);
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
