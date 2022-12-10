<?php

namespace Mush\Disease\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionEffectSubscriber implements EventSubscriberInterface
{
    public const MAKE_SICK_DELAY_MIN = 1;
    public const MAKE_SICK_DELAY_LENGTH = 4;

    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;

    public function __construct(
        DiseaseCauseServiceInterface $diseaseCauseService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
    ) {
        $this->diseaseCauseService = $diseaseCauseService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::CONSUME => 'onConsume',
            ApplyEffectEvent::HEAL => 'onHeal',
            ApplyEffectEvent::PLAYER_GET_SICK => 'onPlayerGetSick',
            ApplyEffectEvent::PLAYER_CURE_INJURY => 'onPlayerCureInjury',
        ];
    }

    /**
     * @return void
     */
    public function onConsume(ApplyEffectEvent $event)
    {
        $equipment = $event->getParameter();

        if (!$equipment instanceof GameEquipment) {
            return;
        }

        $this->diseaseCauseService->handleSpoiledFood($event->getPlayer(), $equipment);
        $this->diseaseCauseService->handleConsumable($event->getPlayer(), $equipment);
    }

    /**
     * @return void
     */
    public function onHeal(ApplyEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $diseaseToHeal = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(TypeEnum::DISEASE)->first();

        if (!$diseaseToHeal) {
            return;
        }

        $this->playerDiseaseService->healDisease($event->getPlayer(), $diseaseToHeal, $event->getReason(), $event->getTime());
    }

    /**
     * @return void
     */
    public function onPlayerGetSick(ApplyEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $actionName = $event->getReason();
        if ($actionName === ActionEnum::MAKE_SICK) {
            $this->diseaseCauseService->handleDiseaseForCause(
                $event->getReason(),
                $player,
                self::MAKE_SICK_DELAY_MIN,
                self::MAKE_SICK_DELAY_LENGTH
            );

            return;
        }

        $this->diseaseCauseService->handleDiseaseForCause($event->getReason(), $player);
    }

    /**
     * @return void
     */
    public function onPlayerCureInjury(ApplyEffectEvent $event)
    {
        // Get a random injury on target player
        $targetPlayer = $event->getParameter();

        if (!$targetPlayer instanceof Player) {
            return;
        }

        $injuryToHeal = $this->randomService->getRandomDisease($targetPlayer->getMedicalConditions()->getByDiseaseType(TypeEnum::INJURY));

        $this->playerDiseaseService->removePlayerDisease(
            $injuryToHeal,
            $event->getReason(),
            $event->getTime(),
            $event->getVisibility(),
            $event->getPlayer(),
        );
    }
}
