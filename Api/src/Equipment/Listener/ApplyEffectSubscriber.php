<?php

namespace Mush\Equipment\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Event\Service\EventServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplyEffectSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventServiceInterface $eventService,
        EquipmentEffectServiceInterface $equipmentServiceEffect
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
          $this->eventService = $eventService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::CONSUME => 'onConsume',
        ];
    }

    public function onConsume(ApplyEffectEvent $consumeEvent)
    {
        $player = $consumeEvent->getPlayer();
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

        if (!$player->isMush()) {
            $this->dispatchConsumableEffects($consumableEffect, $player, $ration->hasStatus(EquipmentStatusEnum::FROZEN));
        } else {
            $this->dispatchMushEffect($player);
        }

        // if no charges consume equipment
        $equipmentEvent = new InteractWithEquipmentEvent(
            $ration,
            $player,
            VisibilityEnum::HIDDEN,
            $consumeEvent->getReason(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect, Player $player, bool $isFrozen): void
    {
        if (($delta = $consumableEffect->getActionPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::ACTION_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::MOVEMENT_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null &&
            !($isFrozen && $delta > 0)) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::MORAL_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::SATIETY,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    protected function dispatchMushEffect(Player $player): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            4,
            ActionEnum::CONSUME,
            new \DateTime()
        );
        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
