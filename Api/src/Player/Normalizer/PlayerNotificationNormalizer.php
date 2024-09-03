<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\PlayerNotification;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlayerNotificationNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PlayerNotification;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $notification = $this->playerNotification($object);

        return [
            'title' => $this->translatedNotificationKey('title', $notification),
            'subTitle' => $this->translatedNotificationKey('subTitle', $notification),
            'description' => $this->translatedNotificationKey('description', $notification),
        ];
    }

    private function playerNotification(mixed $object): PlayerNotification
    {
        if (!$object instanceof PlayerNotification) {
            throw new \RuntimeException('The object is not a PlayerNotification');
        }

        return $object;
    }

    private function translatedNotificationKey(string $key, PlayerNotification $notification): string
    {
        $key = \sprintf('%s.%s', $notification->getMessage(), $key);
        $translation = $this->translationService->translate(
            key: $key,
            parameters: $notification->getParameters(),
            domain: 'notification',
            language: $notification->getLanguage(),
        );

        return $translation !== $key ? $translation : '';
    }
}
