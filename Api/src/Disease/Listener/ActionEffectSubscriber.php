<?php

namespace Mush\Disease\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
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
            ApplyEffectEvent::ULTRA_HEAL => 'onUltraHeal',
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

        $this->diseaseCauseService->handleSpoiledFood($event->getAuthor(), $equipment);
        $this->diseaseCauseService->handleConsumable($event->getAuthor(), $equipment);
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

        $diseaseToHeal = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->first();

        if (!$diseaseToHeal) {
            return;
        }

        $this->playerDiseaseService->healDisease($event->getAuthor(), $diseaseToHeal, $event->getTags(), $event->getTime());
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

        $actionName = current($event->getTags());

        $this->diseaseCauseService->handleDiseaseForCause($actionName, $player);
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

        $injuryToHeal = $this->randomService->getRandomDisease($targetPlayer->getMedicalConditions()->getByDiseaseType(MedicalConditionTypeEnum::INJURY));

        $this->playerDiseaseService->removePlayerDisease(
            $injuryToHeal,
            $event->getTags(),
            $event->getTime(),
            $event->getVisibility(),
            $event->getAuthor(),
        );
    }

    /**
     * @return void
     */
    public function onUltraHeal(ApplyEffectEvent $event)
    {
        $player = $event->getParameter();

        if (!$player instanceof Player) {
            return;
        }

        $diseases = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE);
        $injuries = $player->getMedicalConditions()->getActiveDiseases()->getByDiseaseType(MedicalConditionTypeEnum::INJURY);
        $diseasesAndInjuries = array_merge($diseases->toArray(), $injuries->toArray());

        foreach ($diseasesAndInjuries as $disease) {
            $this->playerDiseaseService->removePlayerDisease(
                $disease,
                $event->getTags(),
                $event->getTime(),
                $event->getVisibility(),
                $event->getAuthor()
            );
        }
    }
}
