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
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->modifierCreationService = $modifierCreationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => [['onStatusApplied'], ['appliesDirectModifiers']],
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

            if (
                $statusHolder instanceof ModifierProviderInterface
            ) {
                $modifierProvider = $statusHolder;
            } else {
                $modifierProvider = $event->getStatus();
            }

            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $modifierProvider,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusHolder = $event->getStatusHolder();

        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null) {
                return;
            }

            $this->modifierCreationService->deleteModifier($modifierConfig, $modifierHolder, $event->getTags(), $event->getTime());
        }
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
                tags: $event->getTags(),
                time: $event->getTime(),
                reverse: false
            );
        }
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
}
