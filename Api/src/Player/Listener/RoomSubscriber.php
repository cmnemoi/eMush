<?php

namespace Mush\Player\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RoomSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DeleteEquipmentServiceInterface $deleteEquipment,
        private EventServiceInterface $eventService,
        private GameEquipmentService $gameEquipmentService,
        private PlayerServiceInterface $playerService,
        private RandomServiceInterface $randomService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($difficultyConfig->getTremorPlayerDamage());

            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                [EndCauseEnum::INJURY],
                $event->getTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($difficultyConfig->getElectricArcPlayerDamage());

            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                [EndCauseEnum::ELECTROCUTED],
                $event->getTime()
            );
            $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        foreach ($event->getPlace()->getPlayers() as $player) {
            $gameEquipments = $this->gameEquipmentService->findByOwner($player);

            /** @var GameEquipment $gameEquipment */
            foreach ($gameEquipments as $gameEquipment) {
                $this->deleteEquipment->execute($gameEquipment, tags: $event->getTags(), time: $event->getTime());
            }

            $this->playerService->delete($player);
        }
    }
}
