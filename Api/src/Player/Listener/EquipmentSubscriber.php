<?php

namespace Mush\Player\Listener;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventServiceInterface $eventService,
        PlayerServiceInterface $playerService
    ) {
        $this->eventService = $eventService;
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $patrolShip = $event->getGameEquipment();
        $patrolShipSpace = $event->getPlace();
        // only handle patrol ship destructions
        if (!EquipmentEnum::getPatrolShips()->contains($patrolShip->getName())) {
            return;
        }

        $players = $patrolShipSpace->getPlayers();

        foreach ($players as $player) {
            $this->ejectPlayer($player, $event->getTags(), $event->getTime());
        }
    }

    private function ejectPlayer(Player $player, array $tags, \DateTime $time): void
    {
        // move player to the space instead of landing bay
        $player->changePlace($player->getDaedalus()->getSpace());
        $this->playerService->persist($player);

        // kill player if they don't have an operational spacesuit
        if (!$player->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)) {
            $deathPlayerEvent = new PlayerEvent(
                $player,
                $tags,
                $time
            );
            $this->eventService->callEvent($deathPlayerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }
}
