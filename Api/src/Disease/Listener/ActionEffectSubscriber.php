<?php

namespace Mush\Disease\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionEffectSubscriber implements EventSubscriberInterface
{
    public const MAKE_SICK_DELAY_MIN = 1;
    public const MAKE_SICK_DELAY_LENGTH = 4;

    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(
        DiseaseCauseServiceInterface $diseaseCauseService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        $this->diseaseCauseService = $diseaseCauseService;
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::CONSUME => 'onConsume',
            ApplyEffectEvent::HEAL => 'onHeal',
            ApplyEffectEvent::PLAYER_GET_SICK => 'onPlayerGetSick',
        ];
    }

    public function onConsume(ApplyEffectEvent $event)
    {
        $equipment = $event->getParameter();

        if (!$equipment instanceof GameEquipment) {
            return;
        }

        $this->diseaseCauseService->handleSpoiledFood($event->getPlayer(), $equipment);
        $this->diseaseCauseService->handleConsumable($event->getPlayer(), $equipment);
    }

    public function onHeal(ApplyEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $diseases = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(TypeEnum::DISEASE);

        foreach ($diseases as $disease) {
            $this->playerDiseaseService->healDisease($event->getPlayer(), $disease, $event->getReason(), $event->getTime());
        }
    }

    public function onPlayerGetSick(ApplyEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $actionName = $event->getReason();
        if ($actionName === ActionEnum::MAKE_SICK) {
            $this->playerDiseaseService->handleDiseaseForCause(
                $event->getReason(),
                $player,
                self::MAKE_SICK_DELAY_MIN,
                self::MAKE_SICK_DELAY_LENGTH
            );

            return;
        }

        $this->playerDiseaseService->handleDiseaseForCause($event->getReason(), $player);
    }
}
