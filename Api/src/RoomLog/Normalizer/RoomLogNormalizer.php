<?php

namespace Mush\RoomLog\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
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
        return $data instanceof RoomLog;
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
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
