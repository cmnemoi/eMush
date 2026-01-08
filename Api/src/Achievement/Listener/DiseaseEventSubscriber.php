<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DiseaseEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DiseaseEvent::APPEAR_DISEASE => 'onAppearDisease',
            DiseaseEvent::CURE_DISEASE => 'onCureDisease',
        ];
    }

    public function onAppearDisease(DiseaseEvent $event): void
    {
        if ($event->getDiseaseConfig()->getType() !== MedicalConditionTypeEnum::DISEASE) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->getTargetPlayer(),
            statisticName: StatisticEnum::DISEASE_CONTRACTED,
        );
    }

    public function onCureDisease(DiseaseEvent $event): void
    {
        $author = $event->getAuthor();

        // If consuming something that cures the disease, the player should get the score
        if ($event->hasAnyTag([DiseaseStatusEnum::HEALED, DiseaseStatusEnum::DRUG_HEALED])) {
            $author = $event->getTargetPlayer();
        }

        if (!$author) {
            return;
        }

        $statisticName = match ($event->getDiseaseConfig()->getType()) {
            MedicalConditionTypeEnum::DISORDER => StatisticEnum::SHRINKER,
            MedicalConditionTypeEnum::DISEASE => StatisticEnum::PHYSICIAN,
            default => StatisticEnum::NULL
        };

        $this->updatePlayerStatisticService->execute(
            player: $author,
            statisticName: $statisticName,
        );
    }
}
