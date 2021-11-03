<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\GearModifierService;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private GearModifierService $gearModifierService;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        GearModifierService $gearModifierService,
        ModifierServiceInterface $modifierService,
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierService = $modifierService;
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

        $holder = $event->getStatusHolder();

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            switch (true) {
                case $holder instanceof Player:
                    $this->modifierService->createModifier($modifierConfig, $holder->getDaedalus(), $holder->getPlace(), $holder, null);

                    return;
                case $holder instanceof Place:
                    $this->modifierService->createModifier($modifierConfig, $holder->getDaedalus(), $holder, null, null);

                    return;
                case $holder instanceof GameEquipment:
                    $this->modifierService->createModifier($modifierConfig, $holder->getPlace()->getDaedalus(), $holder->getPlace(), null, $holder);

                    return;
            }
        }

        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$holder instanceof GameEquipment) {
                throw new UnexpectedTypeException($holder, GameEquipment::class);
            }
            $this->gearModifierService->gearDestroyed($holder);
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$holder instanceof GameEquipment) {
                throw new UnexpectedTypeException($holder, GameEquipment::class);
            }
            $this->gearModifierService->gearCreated($holder);
        }
    }
}
