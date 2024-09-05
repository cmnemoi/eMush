<?php

namespace Mush\Equipment\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplyEffectSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;

    public function __construct(
        EventServiceInterface $eventService,
        EquipmentEffectServiceInterface $equipmentServiceEffect
    ) {
        $this->eventService = $eventService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::CONSUME => ['onConsume', -10],
        ];
    }

    public function onConsume(ApplyEffectEvent $consumeEvent)
    {
        $player = $consumeEvent->getAuthor();
        $ration = $consumeEvent->getParameter();

        if (!$ration instanceof GameItem) {
            return;
        }

        /** @var Ration $rationType */
        $rationType = $ration->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        $consumableEffect = $this->equipmentServiceEffect->getConsumableEffect($rationType, $player->getDaedalus());

        if ($player->isHuman()) {
            $this->dispatchContaminatedFoodEffect($ration, $player, $consumeEvent->getTags());
            $this->dispatchConsumableEffects($consumableEffect, $player, $ration);
        } else {
            $this->dispatchMushEffect($player);
        }

        // if no charges consume equipment
        $equipmentEvent = new InteractWithEquipmentEvent(
            $ration,
            $player,
            VisibilityEnum::HIDDEN,
            $consumeEvent->getTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect, Player $player, GameEquipment $ration): void
    {
        $isFrozen = $ration->hasStatus(EquipmentStatusEnum::FROZEN);
        $tags = $this->createEventTags($ration);

        if (($delta = $consumableEffect->getActionPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::ACTION_POINT,
                $delta,
                $tags,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::MOVEMENT_POINT,
                $delta,
                $tags,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                $delta,
                $tags,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null
            && !($isFrozen && $delta > 0)) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::MORAL_POINT,
                $delta,
                $tags,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::SATIETY,
                $delta,
                $tags,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    protected function dispatchMushEffect(Player $player): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            4,
            [ActionEnum::CONSUME->value],
            new \DateTime()
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function dispatchContaminatedFoodEffect(GameEquipment $ration, Player $player, array $tags): void
    {
        if ($ration->doesNotHaveStatus(EquipmentStatusEnum::CONTAMINATED)) {
            return;
        }

        $contaminatedStatus = $ration->getChargeStatusByNameOrThrow(EquipmentStatusEnum::CONTAMINATED);

        $playerModifierEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::SPORE,
            quantity: $contaminatedStatus->getCharge(),
            tags: $tags,
            time: new \DateTime()
        );
        $playerModifierEvent->setAuthor($contaminatedStatus->getPlayerTargetOrThrow());
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function createEventTags(GameEquipment $ration): array
    {
        $tags = [ActionEnum::CONSUME->value, $ration->getName()];
        if ($ration->hasMechanicByName(EquipmentMechanicEnum::FRUIT)) {
            $tags[] = EquipmentMechanicEnum::FRUIT;
        }
        if ($ration->hasMechanicByName(EquipmentMechanicEnum::DRUG)) {
            $tags[] = EquipmentMechanicEnum::DRUG;
        }

        return $tags;
    }
}
