<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Service\SymptomConditionServiceInterface;
use Mush\Disease\Service\SymptomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private SymptomServiceInterface $symptomService;
    private SymptomConditionServiceInterface $symptomConditionService;

    public function __construct(
        SymptomServiceInterface $symptomService,
        SymptomConditionServiceInterface $symptomConditionService,
    ) {
        $this->symptomService = $symptomService;
        $this->symptomConditionService = $symptomConditionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        $statusHolder = $event->getStatusHolder();
        // we only care on players here as we want to get symptoms triggered by player statuses
        if (!$statusHolder instanceof Player) {
            return;
        }
        $player = $statusHolder;

        $statusAppliedSymptomConfigs = $this->getPlayerSymptomConfigs($player)->getTriggeredSymptoms([StatusEvent::STATUS_APPLIED]);
        $statusAppliedSymptomConfigs = $this->symptomConditionService->getActiveSymptoms($statusAppliedSymptomConfigs, $player, $statusConfig->getStatusName());

        foreach ($statusAppliedSymptomConfigs as $symptomConfig) {
            $this->symptomService->handleStatusAppliedSymptom($symptomConfig, $player, $event->getTime());
        }
    }

    private function getPlayerSymptomConfigs(Player $player): SymptomConfigCollection
    {
        return $player->getMedicalConditions()->getActiveDiseases()->getAllSymptoms();
    }
}
