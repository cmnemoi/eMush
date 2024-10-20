<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public const int NB_ORGANIC_WASTE_MIN = 3;
    public const int NB_ORGANIC_WASTE_MAX = 4;

    public function __construct(
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', EventPriorityEnum::NORMAL],
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

        if ($player->isExploringOrIsLostOnPlanet()) {
            foreach ($playerEquipment as $item) {
                $this->deleteEquipment->execute($item, tags: $event->getTags(), time: $event->getTime());
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
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: GameRationEnum::ORGANIC_WASTE,
            equipmentHolder: $player->getPlace(),
            reasons: [EndCauseEnum::QUARANTINE],
            time: $event->getTime(),
            quantity: $this->getRandomInteger->execute(self::NB_ORGANIC_WASTE_MIN, self::NB_ORGANIC_WASTE_MAX),
        );
    }
}
