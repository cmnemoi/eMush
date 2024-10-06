<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public const int NB_ORGANIC_WASTE_MIN = 3;
    public const int NB_ORGANIC_WASTE_MAX = 4;
    private EventServiceInterface $eventService;

    public function __construct(
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        EventServiceInterface $eventService,
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->handlePlayerEquipment($event);

        if ($event->hasTag(EndCauseEnum::QUARANTINE)) {
            $this->handleQuarantineCompensation($event);
        }
    }

    private function handlePlayerEquipment(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $playerEquipment = $player->getEquipments();

        if ($player->isExploringOrIsLostOnPlanet() || ($player->isInAPatrolShip() && $event->hasTag(EndCauseEnum::ABANDONED))) {
            foreach ($playerEquipment as $item) {
                $destroyEvent = new EquipmentEvent(
                    $item,
                    false,
                    VisibilityEnum::HIDDEN,
                    [],
                    new \DateTime(),
                );
                $this->eventService->callEvent($destroyEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
            }

            return;
        }

        foreach ($playerEquipment as $item) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $item,
                newHolder: $player->getPlace(),
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }

    private function handleQuarantineCompensation(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $place = $player->getPlace();
        if ($player->isExploringOrIsLostOnPlanet() || $player->isInAPatrolShip()) {
            $place = $player->getDaedalus()->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        }

        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: GameRationEnum::ORGANIC_WASTE,
            equipmentHolder: $place,
            reasons: [EndCauseEnum::QUARANTINE],
            time: $event->getTime(),
            quantity: $this->getRandomInteger->execute(self::NB_ORGANIC_WASTE_MIN, self::NB_ORGANIC_WASTE_MAX),
        );
    }
}
