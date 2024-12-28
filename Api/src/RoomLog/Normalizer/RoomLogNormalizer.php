<?php

namespace Mush\RoomLog\Normalizer;

use Mush\Game\Service\DateProviderInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoomLogNormalizer implements NormalizerInterface
{
    public function __construct(
        private DateProviderInterface $dateProvider,
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof RoomLogCollection;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [RoomLogCollection::class => false];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $collection = $this->roomLogCollectionFrom($object);
        $currentPlayer = $this->currentPlayerFrom($context);

        $logs = [];
        foreach ($collection as $roomLog) {
            $log = $this->normalizeRoomLog($roomLog, $currentPlayer);
            $dayKey = $this->getDayKey($roomLog, $currentPlayer);
            $cycleKey = $this->getCycleKey($roomLog, $currentPlayer);

            if ($this->isPlayerPlaceDelogged($currentPlayer)) {
                $logs[$dayKey][$cycleKey] = [];
            }
            $logs[$dayKey][$cycleKey][] = $log;
        }

        return $logs;
    }

    private function normalizeRoomLog(RoomLog $roomLog, Player $currentPlayer): array
    {
        $log = [
            'id' => $roomLog->getId(),
            'log' => $this->getTranslatedLog($roomLog, $currentPlayer),
            'visibility' => $roomLog->getVisibility(),
            'isUnread' => $roomLog->isUnreadBy($currentPlayer),
        ];

        if (!$this->isPlayerPlaceDelogged($currentPlayer)) {
            $log['date'] = $this->getLogDate($roomLog->getCreatedAt() ?: new \DateTime('now'), $currentPlayer->getDaedalus()->getLanguage());
        }

        return $log;
    }

    private function getTranslatedLog(RoomLog $roomLog, Player $currentPlayer): string
    {
        $parameters = $roomLog->getParameters();

        return $this->translationService->translate(
            $roomLog->getLog(),
            array_merge($parameters, ['is_tracker' => $currentPlayer->hasSkill(SkillEnum::TRACKER) ? 'true' : 'false']),
            $roomLog->getType(),
            $currentPlayer->getDaedalus()->getLanguage()
        );
    }

    private function getDayKey(RoomLog $roomLog, Player $currentPlayer): int|string
    {
        return $this->isPlayerPlaceDelogged($currentPlayer) ? '?' : $roomLog->getDay();
    }

    private function getCycleKey(RoomLog $roomLog, Player $currentPlayer): int|string
    {
        return $this->isPlayerPlaceDelogged($currentPlayer) ? '?' : $roomLog->getCycle();
    }

    private function isPlayerPlaceDelogged(Player $currentPlayer): bool
    {
        return $currentPlayer->getPlace()->hasStatus(PlaceStatusEnum::DELOGGED->toString());
    }

    private function getLogDate(\DateTime $logDate, string $language): string
    {
        $now = $this->dateProvider->now();
        $interval = $now->diff($logDate);

        if ($interval->days > 0) {
            return $this->translationService->translate(
                'message_date.more_day',
                ['quantity' => $interval->days],
                'chat',
                $language
            );
        }

        if ($interval->h > 0) {
            return $this->translationService->translate(
                'message_date.more_hour',
                ['quantity' => $interval->h],
                'chat',
                $language
            );
        }

        if ($interval->i > 0) {
            return $this->translationService->translate(
                'message_date.more_minute',
                ['quantity' => $interval->i],
                'chat',
                $language
            );
        }

        return $this->translationService->translate(
            'message_date.less_minute',
            [],
            'chat',
            $language
        );
    }

    private function roomLogCollectionFrom(mixed $object): RoomLogCollection
    {
        if (!$object instanceof RoomLogCollection) {
            throw new \InvalidArgumentException(\sprintf('Expected RoomLogCollection, got %s', \get_class($object)));
        }

        return $object;
    }

    private function currentPlayerFrom(array $context): Player
    {
        if (!isset($context['currentPlayer'])) {
            throw new \InvalidArgumentException('currentPlayer is required in context');
        }

        return $context['currentPlayer'] instanceof Player
            ? $context['currentPlayer']
            : throw new \InvalidArgumentException('currentPlayer is required in context');
    }
}
