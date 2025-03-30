<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
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

    public function __construct(
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private EventServiceInterface $eventService,
        private GameEquipmentRepository $gameEquipmentRepository,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomIntegerServiceInterface $getRandomInteger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->handlePlayerEquipment($event);

        if ($event->hasTag(EndCauseEnum::QUARANTINE)) {
            $this->handleQuarantineCompensation($event);
        }
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $this->createPlayerStartingItems($event);
    }

    private function handlePlayerEquipment(PlayerEvent $event): void
    {
        $this->destroyPlayerPersonalItemsInDaedalus($event);

        $player = $event->getPlayer();
        $playerEquipment = $player->getEquipments();

        if ($player->isExploringOrIsLostOnPlanet() || $event->hasTag(EndCauseEnum::ABANDONED)) {
            $this->destroyPlayerItems($event);

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

    private function destroyPlayerItems(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getEquipments() as $item) {
            $this->deleteEquipment->execute($item, tags: $event->getTags(), time: $event->getTime());
        }
    }

    private function destroyPlayerPersonalItemsInDaedalus(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $criteria = new GameEquipmentCriteria($event->getDaedalus());
        $criteria->setPersonal(true);

        $personalItems = $this->gameEquipmentRepository->findByCriteria($criteria);

        foreach ($personalItems as $item) {
            if ($item->getOwner()?->equals($player)) {
                $this->deleteEquipment->execute($item, tags: $event->getTags(), time: $event->getTime());
            }
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

    private function createPlayerStartingItems(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $characterConfig = $player->getPlayerInfo()->getCharacterConfig();

        foreach ($characterConfig->getStartingItems() as $itemConfig) {
            $this->gameEquipmentService->createGameEquipment(
                equipmentConfig: $itemConfig,
                holder: $player,
                reasons: [PlayerEvent::NEW_PLAYER],
                time: $event->getTime(),
                visibility: VisibilityEnum::PRIVATE
            );
        }
    }
}
