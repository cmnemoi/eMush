<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Disease\Service\SymptomConditionServiceInterface;
use Mush\Disease\Service\SymptomServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private SymptomServiceInterface $symptomService;
    private SymptomConditionServiceInterface $symptomConditionService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        SymptomServiceInterface $symptomService,
        SymptomConditionServiceInterface $symptomConditionService)
    {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->symptomService = $symptomService;
        $this->symptomConditionService = $symptomConditionService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onPlayerNewCycle',
        ];
    }

    public function onPlayerNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getMedicalConditions() as $disease) {
            $this->playerDiseaseService->handleNewCycle($disease, $event->getTime());
        }

        $cycleSymptomConfigs = $this->getPlayerSymptomConfigs($player)->getTriggeredSymptoms([EventEnum::NEW_CYCLE]);
        $cycleSymptomConfigs = $this->symptomConditionService->getActiveSymptoms($cycleSymptomConfigs, $player, EventEnum::NEW_CYCLE);

        foreach ($cycleSymptomConfigs as $symptomConfig) {
            $this->symptomService->handleCycleSymptom($symptomConfig, $player, $event->getTime());
        }
    }

    private function getPlayerSymptomConfigs(Player $player): SymptomConfigCollection
    {
        $playerDiseases = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(TypeEnum::DISEASE);

        $symptomConfigs = new SymptomConfigCollection();
        foreach ($playerDiseases as $disease) {
            foreach ($disease->getDiseaseConfig()->getSymptomConfigs() as $symptomConfig) {
                if (!$symptomConfigs->contains($symptomConfig)) {
                    $symptomConfigs->add($symptomConfig);
                }
            }
        }

        return $symptomConfigs;
    }
}
