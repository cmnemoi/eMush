<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $gearModifierService;
    private ModifierCreationServiceInterface $modifierCreationService;
    private ModifierRequirementServiceInterface $modifierActivationRequirementService;
    private EventServiceInterface $eventService;

    public function __construct(
        EquipmentModifierServiceInterface $gearModifierService,
        ModifierCreationServiceInterface $modifierCreationService,
        ModifierRequirementServiceInterface $modifierActivationRequirementService,
        EventServiceInterface $eventService
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierCreationService = $modifierCreationService;
        $this->modifierActivationRequirementService = $modifierActivationRequirementService;
        $this->eventService = $eventService;
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
            if ($modifierHolder === null || !($modifierConfig instanceof EventModifierConfig)) {
                return;
            }

            $this->modifierCreationService->createModifier(
                $modifierConfig,
                $modifierHolder,
                $event->getTags(),
                $event->getTime(),
                null
            );
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }
            $this->gearModifierService->gearDestroyed($statusHolder, $event->getTags(), $event->getTime());
        }

        // handle modifiers triggered by player status
        $player = $this->getPlayer($statusHolder);
        if ($player !== null) {
            $modifiers = $player->getModifiers()->getScopedModifiers([StatusEvent::STATUS_APPLIED]);
            $modifiers = $this->modifierActivationRequirementService->getActiveModifiers($modifiers, $event->getTags(), $player);

            /** @var GameModifier $modifier */
            foreach ($modifiers as $modifier) {
                $this->createQuantityEvent($player, $modifier, $event->getTime(), $event->getTags());
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
            $this->gearModifierService->gearCreated($statusHolder, $event->getTags(), $event->getTime());
        }

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null || !($modifierConfig instanceof EventModifierConfig)) {
                return;
            }

            $this->modifierCreationService->deleteModifier($modifierConfig, $modifierHolder, $event->getTags(), $event->getTime(), null);
        }
    }

    private function getModifierHolderFromConfig(StatusHolderInterface $statusHolder, AbstractModifierConfig $modifierConfig): ?ModifierHolder
    {
        switch ($modifierConfig->getModifierRange()) {
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
        return $statusHolder->getDaedalus();
    }

    private function getPlace(StatusHolderInterface $statusHolder): ?Place
    {
        return $statusHolder->getPlace();
    }

    private function getPlayer(StatusHolderInterface $statusHolder): ?Player
    {
        return $statusHolder->getPlayer();
    }

    private function getEquipment(StatusHolderInterface $statusHolder): ?GameEquipment
    {
        return $statusHolder->getGameEquipment();
    }

    private function createQuantityEvent(ModifierHolder $holder, GameModifier $modifier, \DateTime $time, array $eventReasons): void
    {
        $modifierConfig = $modifier->getModifierConfig();

        if ($modifierConfig instanceof VariableEventModifierConfig &&
            $holder instanceof Player
        ) {
            $target = $modifierConfig->getTargetVariable();
            $value = intval($modifierConfig->getDelta());
            $eventReasons[] = $modifierConfig->getModifierName();

            $event = new PlayerVariableEvent(
                $holder,
                $target,
                $value,
                $eventReasons,
                $time,
            );

            $this->eventService->callEvent($event, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
