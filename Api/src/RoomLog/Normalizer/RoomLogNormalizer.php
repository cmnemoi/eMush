<?php

namespace Mush\RoomLog\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoomLogNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof RoomLogCollection && !\in_array('moderation_read', $context['groups'] ?? [], true);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var RoomLogCollection $logCollection */
        $logCollection = $object;

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $language = $currentPlayer->getDaedalus()->getLanguage();

        $logs = [];
        foreach ($logCollection as $roomLog) {
            $parameters = $roomLog->getParameters();
            $parameters['is_tracker'] = $currentPlayer->hasSkill(SkillEnum::TRACKER) ? 'true' : 'false';
            $log = [
                'id' => $roomLog->getId(),
                'log' => $this->translationService->translate(
                    $roomLog->getLog(),
                    $parameters,
                    $roomLog->getType(),
                    $language
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $this->getLogDate($roomLog->getCreatedAt() ?: new \DateTime('now'), $language),
                'isUnread' => $roomLog->isUnreadBy($currentPlayer),
            ];

            $logs[$roomLog->getDay()][$roomLog->getCycle()][] = $log;
        }

        return $logs;
    }

    private function getLogDate(\DateTime $dateTime, string $language): string
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
