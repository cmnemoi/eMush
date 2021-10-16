<?php

namespace Mush\Equipment\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEventInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEventInterface;
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
            ApplyEffectEventInterface::CONSUME => 'onConsume',
        ];
    }

    public function onConsume(ApplyEffectEventInterface $consumeEvent)
    {
        $player = $consumeEvent->getPlayer();
        $ration = $consumeEvent->getParameter();

        if (!$ration instanceof GameItem) {
            return;
        }

        $rationType = $ration->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        $consumableEffect = $this->equipmentServiceEffect->getConsumableEffect($rationType, $player->getDaedalus());

        if (!$player->isMush()) {
            $this->dispatchConsumableEffects($consumableEffect, $player);
        } else {
            $this->dispatchMushEffect($player);
        }

        // if no charges consume equipment
        $this->gameEquipmentService->delete($ration);
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect, Player $player): void
    {
        if (($delta = $consumableEffect->getActionPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::ACTION_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::MOVEMENT_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::HEALTH_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::MORAL_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                $delta,
                ActionEnum::CONSUME,
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
        }
    }

    protected function dispatchMushEffect(Player $player): void
    {
        $playerModifierEvent = new PlayerModifierEventInterface(
            $player,
            4,
            ActionEnum::CONSUME,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::SATIETY_POINT_MODIFIER);
    }
}
