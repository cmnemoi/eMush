<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CommanderMissionNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CommanderMission;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $commanderMission = $this->commanderMission($object);

        return [
            'id' => $commanderMission->getId(),
            'commander' => [
                'id' => $commanderMission->getCommander()->getId(),
                'key' => $commanderMission->getCommanderName(),
                'name' => $this->translationService->translate(
                    key: \sprintf('%s.name', $commanderMission->getCommanderName()),
                    parameters: [],
                    domain: 'characters',
                    language: $commanderMission->getLanguage(),
                ),
            ],
            'mission' => $commanderMission->getMission(),
            'date' => $this->getTranslatedDate($commanderMission->getCreatedAtOrThrow(), $commanderMission->getLanguage()),
            'isPending' => $commanderMission->isPending(),
            'isCompleted' => $commanderMission->isCompleted(),
        ];
    }

    private function commanderMission(mixed $object): CommanderMission
    {
        return $object instanceof CommanderMission ? $object : throw new \InvalidArgumentException('This normalizer only supports CommanderMission objects');
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
