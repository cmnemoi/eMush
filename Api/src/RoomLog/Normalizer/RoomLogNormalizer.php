<?php

namespace Mush\RoomLog\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class RoomLogNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof RoomLogCollection;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var RoomLogCollection $roomLogCollection */
        $roomLogCollection = $object;

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getGameConfig()->getLanguage();

        return $this->normalizeLogs($roomLogCollection, $language);
    }

    public function normalizeLogs(RoomLogCollection $logCollection, string $language): array
    {
        $logs = [];
        foreach ($logCollection as $roomLog) {
            $logs[$roomLog->getDay()][$roomLog->getCycle()][] = [
                'log' => $this->translationService->translate(
                    $roomLog->getLog(),
                    $roomLog->getParameters(),
                    $roomLog->getType(),
                    $language
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $this->getLogDate($roomLog->getDate(), $language),
            ];
        }

        return $logs;
    }

    private function getLogDate(\DateTime $dateTime, string $language): string
    {
        $dateInterval = $dateTime->diff(new \DateTime());

        $days = intval($dateInterval->format('%a'));
        $hours = intval($dateInterval->format('%H'));
        $minutes = intval($dateInterval->format('%i'));

        if ($days > 0) {
            return $this->translationService->translate('message_date.more_day', ['quantity' => $days], 'chat', $language);
        } elseif ($hours > 0) {
            return $this->translationService->translate('message_date.more_hour', ['quantity' => $hours], 'chat', $language);
        } elseif ($minutes > 0) {
            return $this->translationService->translate('message_date.more_minute', ['quantity' => $minutes], 'chat', $language);
        } else {
            return $this->translationService->translate('message_date.less_minute', [], 'chat', $language);
        }
    }
}
