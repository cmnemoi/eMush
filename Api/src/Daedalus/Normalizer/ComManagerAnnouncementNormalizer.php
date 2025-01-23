<?php

declare(strict_types=1);

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\ComManagerAnnouncement;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ComManagerAnnouncementNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function getSupportedTypes(?string $format): array
    {
        return [
            ComManagerAnnouncement::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ComManagerAnnouncement;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $comManagerAnnouncement = $this->comManagerAnnouncement($object);

        return [
            'id' => $comManagerAnnouncement->getId(),
            'comManager' => [
                'id' => $comManagerAnnouncement->getComManager()->getId(),
                'key' => $comManagerAnnouncement->getComManagerName(),
                'name' => $this->translationService->translate(
                    key: \sprintf('%s.name', $comManagerAnnouncement->getComManagerName()),
                    parameters: [],
                    domain: 'characters',
                    language: $comManagerAnnouncement->getLanguage(),
                ),
            ],
            'announcement' => $this->translationService->translate(
                key: $comManagerAnnouncement->getAnnouncement(),
                parameters: [],
                domain: 'chat',
                language: $comManagerAnnouncement->getLanguage(),
            ),
            'date' => $this->getTranslatedDate($comManagerAnnouncement->getCreatedAtOrThrow(), $comManagerAnnouncement->getLanguage()),
        ];
    }

    private function comManagerAnnouncement(mixed $object): ComManagerAnnouncement
    {
        return $object instanceof ComManagerAnnouncement ? $object : throw new \InvalidArgumentException('This normalizer only supports ComManagerAnnouncement objects');
    }

    private function getTranslatedDate(\DateTime $dateTime, string $language): string
    {
        $dateInterval = $dateTime->diff(new \DateTime());

        $days = (int) $dateInterval->format('%a');
        $hours = (int) $dateInterval->format('%H');
        $minutes = (int) $dateInterval->format('%i');

        if ($days > 0) {
            return $this->translationService->translate('message_date.more_day', ['quantity' => $days], 'chat', $language);
        }
        if ($hours > 0) {
            return $this->translationService->translate('message_date.more_hour', ['quantity' => $hours], 'chat', $language);
        }
        if ($minutes > 0) {
            return $this->translationService->translate('message_date.more_minute', ['quantity' => $minutes], 'chat', $language);
        }

        return $this->translationService->translate('message_date.less_minute', [], 'chat', $language);
    }
}
