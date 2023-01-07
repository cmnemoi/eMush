<?php

namespace Mush\Disease\Listener;

use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Disease\Service\SymptomActivationRequirementServiceInterface;
use Mush\Disease\Service\SymptomServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private SymptomServiceInterface $symptomService;
    private SymptomActivationRequirementServiceInterface $symptomActivationRequirementService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        SymptomServiceInterface $symptomService,
        SymptomActivationRequirementServiceInterface $symptomActivationRequirementService)
    {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->symptomService = $symptomService;
        $this->symptomActivationRequirementService = $symptomActivationRequirementService;
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
        $cycleSymptomConfigs = $this->symptomActivationRequirementService->getActiveSymptoms($cycleSymptomConfigs, $player, EventEnum::NEW_CYCLE);

        foreach ($cycleSymptomConfigs as $symptomConfig) {
            $this->symptomService->handleCycleSymptom($symptomConfig, $player, $event->getTime());
        }
    }

    private function getPlayerSymptomConfigs(Player $player): SymptomConfigCollection
    {
        $symptomConfigs = $player->getMedicalConditions()->getActiveDiseases()->getAllSymptoms();

        $uniqueSymptomConfigs = new SymptomConfigCollection();
        foreach ($symptomConfigs as $symptomConfig) {
            if (!$uniqueSymptomConfigs->contains($symptomConfig)) {
                $uniqueSymptomConfigs->add($symptomConfig);
            }
        }

        return $symptomConfigs;
    }
}
