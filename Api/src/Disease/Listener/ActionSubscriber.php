<?php

namespace Mush\Disease\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\SymptomConditionServiceInterface;
use Mush\Disease\Service\SymptomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private SymptomServiceInterface $symptomService;
    private SymptomConditionServiceInterface $symptomConditionService;

    public function __construct(SymptomServiceInterface $symptomService, SymptomConditionServiceInterface $symptomConditionService)
    {
        $this->symptomService = $symptomService;
        $this->symptomConditionService = $symptomConditionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $action = $event->getAction();

        $postActionSymptomConfigs = $this->getPlayerSymptomConfigs($player)->getTriggeredSymptoms([ActionEvent::POST_ACTION]);
        $postActionSymptomConfigs = $this->symptomConditionService->getActiveSymptoms($postActionSymptomConfigs, $player, $action->getName());

        foreach ($postActionSymptomConfigs as $symptomConfig) {
            $this->symptomService->handlePostActionSymptom($symptomConfig, $player, $event->getTime());
        }
    }

    private function getPlayerSymptomConfigs(Player $player): SymptomConfigCollection
    {
        $playerDiseases = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(TypeEnum::DISEASE);

        $symptomConfigs = new SymptomConfigCollection([]);
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
