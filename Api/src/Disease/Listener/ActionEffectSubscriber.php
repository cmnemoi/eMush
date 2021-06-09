<?php

namespace Mush\Disease\Listener;

use Mush\Action\Event\ActionEffectEvent;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionEffectSubscriber implements EventSubscriberInterface
{
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(
        DiseaseCauseServiceInterface $diseaseCauseService,
        PlayerDiseaseServiceInterface $playerDiseaseService
    ) {
        $this->diseaseCauseService = $diseaseCauseService;
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEffectEvent::CONSUME => 'onConsume',
            ActionEffectEvent::HEAL => 'onHeal',
        ];
    }

    public function onConsume(ActionEffectEvent $event)
    {
        $equipment = $event->getParameter();

        if (!$equipment instanceof GameEquipment) {
            return;
        }

        $this->diseaseCauseService->handleSpoiledFood($event->getPlayer(), $equipment);
        $this->diseaseCauseService->handleConsumable($event->getPlayer(), $equipment);
    }

    public function onHeal(ActionEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $diseases = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(TypeEnum::DISEASE);

        foreach ($diseases as $disease) {
            $this->playerDiseaseService->healDisease($event->getPlayer(), $disease, new \DateTime());
        }
    }
}
