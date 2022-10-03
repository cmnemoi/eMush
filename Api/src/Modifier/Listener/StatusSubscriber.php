<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Service\EquipmentModifierService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierService $gearModifierService;
    private ModifierServiceInterface $modifierService;
    private EventServiceInterface $eventService;

    public function __construct(
        EquipmentModifierService $gearModifierService,
        ModifierServiceInterface $modifierService,
        EventServiceInterface $eventService
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierService = $modifierService;
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
            if ($modifierHolder === null) {
                continue;
            }

            $this->modifierService->createModifier($modifierConfig, $modifierHolder);
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }

            $this->gearModifierService->destroyGear($statusHolder);
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
            $this->gearModifierService->createGear($statusHolder);
        }

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                continue;
            }

            $this->modifierService->deleteModifier($modifierConfig, $modifierHolder);
        }
    }

    private function getModifierHolderFromConfig(StatusHolderInterface $statusHolder, ModifierConfig $modifierConfig): ?ModifierHolder
    {
        return match ($modifierConfig->getReach()) {
            ModifierReachEnum::DAEDALUS => $this->getDaedalus($statusHolder),
            ModifierReachEnum::PLACE => $this->getPlace($statusHolder),
            ModifierReachEnum::PLAYER, ModifierReachEnum::TARGET_PLAYER => $this->getPlayer($statusHolder),
            ModifierReachEnum::EQUIPMENT => $this->getEquipment($statusHolder),
            default => null,
        };
    }

    private function getDaedalus(StatusHolderInterface $statusHolder): Daedalus
    {
        return match (true) {
            $statusHolder instanceof Player => $statusHolder->getDaedalus(),
            $statusHolder instanceof Place => $statusHolder->getDaedalus(),
            $statusHolder instanceof GameEquipment => $statusHolder->getPlace()->getDaedalus(),
            default => throw new \LogicException('unknown status holder type'),
        };
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
}
