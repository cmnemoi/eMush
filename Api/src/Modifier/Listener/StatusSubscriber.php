<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EquipmentModifierService;
use Mush\Modifier\Service\ModifierRequirementService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierService $gearModifierService;
    private ModifierServiceInterface $modifierService;
    private ModifierRequirementService $modifierActivationRequirementService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EquipmentModifierService $gearModifierService,
        ModifierServiceInterface $modifierService,
        ModifierRequirementService $modifierActivationRequirementService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierService = $modifierService;
        $this->modifierActivationRequirementService = $modifierActivationRequirementService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        $statusHolder = $event->getStatusHolder();

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                return;
            }

            $this->modifierService->createModifier($modifierConfig, $modifierHolder);
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }
            $this->gearModifierService->gearDestroyed($statusHolder);
        }

        // handle modifiers triggered by player status
        $player = $this->getPlayer($statusHolder);
        if ($player !== null) {
            $modifiers = $player->getModifiers()->getScopedModifiers([StatusEvent::STATUS_APPLIED]);
            $modifiers = $this->modifierActivationRequirementService->getActiveModifiers($modifiers, $event->getReason(), $player);

            /** @var GameModifier $modifier */
            foreach ($modifiers as $modifier) {
                $event = $this->createQuantityEvent($player, $modifier, $event->getTime(), $event->getReason());
                $event->setVisibility(VisibilityEnum::HIDDEN);
                $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);
            }
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusHolder = $event->getStatusHolder();

        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }
            $this->gearModifierService->gearCreated($statusHolder);
        }

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                return;
            }

            $this->modifierService->deleteModifier($modifierConfig, $modifierHolder);
        }
    }

    private function getModifierHolderFromConfig(StatusHolderInterface $statusHolder, ModifierConfig $modifierConfig): ?ModifierHolder
    {
        switch ($modifierConfig->getModifierHolderClass()) {
            case ModifierHolderClassEnum::DAEDALUS:
                return $this->getDaedalus($statusHolder);
            case ModifierHolderClassEnum::PLACE:
                return $this->getPlace($statusHolder);
            case ModifierHolderClassEnum::PLAYER:
            case ModifierHolderClassEnum::TARGET_PLAYER:
                return $this->getPlayer($statusHolder);
            case ModifierHolderClassEnum::EQUIPMENT:
                return $this->getEquipment($statusHolder);
        }

        return null;
    }

    private function getDaedalus(StatusHolderInterface $statusHolder): Daedalus
    {
        switch (true) {
            case $statusHolder instanceof Player:
                return $statusHolder->getDaedalus();
            case $statusHolder instanceof Place:
                return $statusHolder->getDaedalus();
            case $statusHolder instanceof GameEquipment:
                return $statusHolder->getDaedalus();
            default:
                throw new \LogicException('unknown statusholder type');
        }
    }

    private function getPlace(StatusHolderInterface $statusHolder): Place
    {
        switch (true) {
            case $statusHolder instanceof Player:
                return $statusHolder->getPlace();
            case $statusHolder instanceof Place:
                return $statusHolder;
            case $statusHolder instanceof GameEquipment:
                return $statusHolder->getPlace();
            default:
                throw new \LogicException('unknown statusholder type');
        }
    }

    private function getPlayer(StatusHolderInterface $statusHolder): ?Player
    {
        switch (true) {
            case $statusHolder instanceof Player:
                return $statusHolder;
            case $statusHolder instanceof Place:
                return null;
            case $statusHolder instanceof GameEquipment:
                if (($player = $statusHolder->getHolder()) instanceof Player) {
                    return $player;
                } else {
                    return null;
                }
                // no break
            default:
                throw new \LogicException('unknown statusholder type');
        }
    }

    private function getEquipment(StatusHolderInterface $statusHolder): ?GameEquipment
    {
        switch (true) {
            case $statusHolder instanceof Player:
            case $statusHolder instanceof Place:
                return null;
            case $statusHolder instanceof GameEquipment:
                return $statusHolder;
            default:
                throw new \LogicException('unknown statusholder type');
        }
    }

    private function createQuantityEvent(ModifierHolder $holder, GameModifier $modifier, \DateTime $time, string $eventReason): PlayerVariableEvent
    {
        $modifierConfig = $modifier->getModifierConfig();

        $target = $modifierConfig->getTargetVariable();
        $value = intval($modifierConfig->getDelta());
        $reason = $modifierConfig->getModifierName() ?: $eventReason;

        switch (true) {
            case $holder instanceof Player:
                return new PlayerVariableEvent(
                    $holder,
                    $target,
                    $value,
                    $reason,
                    $time,
                );
            default:
                throw new \LogicException('Unexpected modifier holder type');
        }
    }
}
