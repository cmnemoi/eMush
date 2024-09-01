<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

final class StatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ModifierCreationServiceInterface $modifierCreationService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => [['onStatusApplied'], ['appliesDirectModifiers']],
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $this->createStatusModifiers($event);
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $this->deleteStatusModifiers($event);
    }

    // Applies direct modifiers already present to the newly created charge status
    public function appliesDirectModifiers(StatusEvent $event): void
    {
        $status = $event->getStatus();

        $statusHolder = $status->getStatusTargetOwner();

        if (
            !($status instanceof ChargeStatus
            && $statusHolder instanceof ModifierHolderInterface)
        ) {
            return;
        }

        $directModifiers = $statusHolder->getAllModifiers()->getDirectModifiers();
        foreach ($directModifiers as $modifier) {
            /** @var DirectModifierConfig $modifierConfig */
            $modifierConfig = $modifier->getModifierConfig();

            $this->modifierCreationService->createDirectModifier(
                modifierConfig: $modifierConfig,
                modifierRange: $statusHolder,
                modifierProvider: $this->getModifierProvider($event),
                tags: $event->getTags(),
                time: $event->getTime(),
                reverse: false
            );
        }
    }

    private function getModifierProvider(StatusEvent $event): ModifierProviderInterface
    {
        $statusHolder = $event->getStatusHolder();
        if (
            $statusHolder instanceof ModifierProviderInterface
        ) {
            return $statusHolder;
        }

        return $event->getStatus();
    }

    private function getModifierHolderFromConfig(StatusHolderInterface $statusHolder, AbstractModifierConfig $modifierConfig): ?ModifierHolderInterface
    {
        return match ($modifierConfig->getModifierRange()) {
            ModifierHolderClassEnum::DAEDALUS => $this->getDaedalus($statusHolder),
            ModifierHolderClassEnum::PLACE => $this->getPlace($statusHolder),
            ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $this->getPlayer($statusHolder),
            ModifierHolderClassEnum::EQUIPMENT => $this->getEquipment($statusHolder),
            default => null,
        };
    }

    private function getDaedalus(StatusHolderInterface $statusHolder): Daedalus
    {
        return $statusHolder->getDaedalus();
    }

    private function getPlace(StatusHolderInterface $statusHolder): ?Place
    {
        if ($statusHolder instanceof Place) {
            return $statusHolder;
        }
        if ($statusHolder instanceof Player) {
            return $statusHolder->getPlace();
        }
        if ($statusHolder instanceof GameEquipment) {
            return $statusHolder->getPlace();
        }

        return null;
    }

    private function getPlayer(StatusHolderInterface $statusHolder): ?Player
    {
        if ($statusHolder instanceof Player) {
            return $statusHolder;
        }
        if ($statusHolder instanceof GameEquipment) {
            return $statusHolder->getPlayer();
        }

        return null;
    }

    private function getEquipment(StatusHolderInterface $statusHolder): ?GameEquipment
    {
        if ($statusHolder instanceof GameEquipment) {
            return $statusHolder;
        }

        return null;
    }

    private function createStatusModifiers(StatusEvent $event): void
    {
        $statusConfig = $event->getStatusConfig();
        $statusHolder = $event->getStatusHolder();

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                return;
            }

            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $this->getModifierProvider($event),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    private function deleteStatusModifiers(StatusEvent $event): void
    {
        $statusConfig = $event->getStatusConfig();
        $statusHolder = $event->getStatusHolder();

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                return;
            }

            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $this->getModifierProvider($event),
                tags: $event->getTags(),
                time: $event->getTime()
            );
        }
    }

