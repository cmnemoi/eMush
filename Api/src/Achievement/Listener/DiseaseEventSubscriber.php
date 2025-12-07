<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
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
}
