<?php

namespace Mush\Modifier\Listener;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseEventEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\DiseaseModifierServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DiseaseModifierServiceInterface $diseaseModifierService,
        private DiseaseCauseServiceInterface $diseaseCauseService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private PlayerDiseaseServiceInterface $playerDiseaseService,
        private RandomServiceInterface $randomService,
        private EventServiceInterface $eventService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DiseaseEvent::APPEAR_DISEASE => 'onDiseaseAppear',
            DiseaseEvent::CURE_DISEASE => 'onDiseaseCured',
        ];
    }

    public function onDiseaseAppear(DiseaseEvent $event): void
    {
        $this->diseaseModifierService->newDisease($event->getTargetPlayer(), $event->getPlayerDisease(), $event->getTags(), $event->getTime());

        $disease = $event->getPlayerDisease();

        // TODO Should be handled with an handler class instead of being coded in the subscriber. Okay-ish for now
        match ($disease->getDiseaseConfig()->getEventWhenAppeared()) {
            DiseaseEventEnum::ADD_CRITICAL_HAEMORRHAGE_100->toString() => $this->handleAddCritHaemorrhage($disease),
            DiseaseEventEnum::ADD_HAEMORRHAGE_20->toString() => $this->handleAddHaemorrhage($disease),
            DiseaseEventEnum::DEAL_6_DMG_ADD_BURN->toString() => $this->handleOedema($disease),
            DiseaseEventEnum::DROP_HEAVY_ITEMS->toString() => $this->handleDropHeavyItems($event),
            default => null
        };
    }

    public function onDiseaseCured(DiseaseEvent $event): void
    {
        $this->diseaseModifierService->cureDisease($event->getTargetPlayer(), $event->getPlayerDisease(), $event->getTags(), $event->getTime());
    }

    private function handleOedema(PlayerDisease $disease): void
    {
        $this->diseaseCauseService->handleDiseaseForCause(
            DiseaseCauseEnum::CAT_ALLERGY,
            $disease->getPlayer()
        );

        $damageEvent = new PlayerVariableEvent(
            $disease->getPlayer(),
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            [$disease->getName(), DiseaseEnum::CAT_ALLERGY->toString()],
            new \DateTime()
        );
        $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function handleAddCritHaemorrhage(PlayerDisease $disease): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::CRITICAL_HAEMORRHAGE->toString(),
            $disease->getPlayer(),
            [DiseaseEventEnum::ADD_CRITICAL_HAEMORRHAGE_100]
        );
    }

    private function handleAddHaemorrhage(PlayerDisease $disease): void
    {
        if ($this->randomService->randomPercent() <= 20) {
            $this->playerDiseaseService->createDiseaseFromName(
                InjuryEnum::HAEMORRHAGE->toString(),
                $disease->getPlayer(),
                [DiseaseEventEnum::ADD_HAEMORRHAGE_20]
            );
        }
    }

    private function handleDropHeavyItems(DiseaseEvent $event): void
    {
        $player = $event->getTargetPlayer();

        if ($player->getPlace()->isNotARoom()) {
            return;
        }

        $tags = $event->getTags();
        $tags[] = DiseaseEventEnum::DROP_HEAVY_ITEMS->toString();

        /** @var GameItem $item */
        foreach ($player->getEquipments() as $item) {
            if ($item->hasStatus(EquipmentStatusEnum::HEAVY)) {
                $this->gameEquipmentService->moveEquipmentTo(
                    $item,
                    $player->getPlace(),
                    VisibilityEnum::PUBLIC,
                    $tags,
                    $event->getTime(),
                    $event->getTargetPlayer()
                );
            }
        }
    }
}
