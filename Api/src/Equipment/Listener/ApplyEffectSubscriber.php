<?php

namespace Mush\Equipment\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplyEffectSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventDispatcherInterface $eventDispatcher,
        EquipmentEffectServiceInterface $equipmentServiceEffect
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
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
        $this->gameEquipmentService->delete($ration);
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect, Player $player, bool $isFrozen): void
    {
        if (($delta = $consumableEffect->getActionPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                PlayerVariableEnum::ACTION_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                PlayerVariableEnum::MOVEMENT_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null &&
            !($isFrozen && $delta > 0)) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                PlayerVariableEnum::MORAL_POINT,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                PlayerVariableEnum::SATIETY,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }

    protected function dispatchMushEffect(Player $player): void
    {
        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            4,
            ActionEnum::CONSUME,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }
}
