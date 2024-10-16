<?php

namespace Mush\Player\Listener;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    public const int GLOBAL_MORALE_LOSS_SCHRODINGER_DEATH = 0;
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
        $equipment = $event->getGameEquipment();
        $equipmentPlace = $event->getPlace();

        // handle patrol ship destructions
        if ($equipment->hasMechanicByName(EquipmentEnum::PATROL_SHIP)) {
            foreach ($equipmentPlace->getPlayers() as $player) {
                $this->ejectPlayer($player, $event->getTags(), $event->getTime());
            }
        }

        // handle morale loss on Schrodinger
        if ($equipment->getName() === ItemEnum::SCHRODINGER) {
            $alivePlayers = $event->getDaedalus()->getAlivePlayers();
            foreach ($alivePlayers as $player) {
                // call for a morale loss of 0 for every player (shouldn't display), then a modifier on cat_owner raises it to 4 for players with the cat_owner status. Doing it this way allows potentially adding a global morale penalty if we ever want to
                $playerVariableEvent = new PlayerVariableEvent(
                    $player,
                    PlayerVariableEnum::MORAL_POINT,
                    self::GLOBAL_MORALE_LOSS_SCHRODINGER_DEATH,
                    [ItemEnum::SCHRODINGER],
                    new \DateTime(),
                );
                $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
            }
        }
    }

    private function ejectPlayer(Player $player, array $tags, \DateTime $time): void
    {
        // move player to the space instead of landing bay
        $this->playerService->changePlace($player, $player->getDaedalus()->getSpace());

        // kill player if they don't have an operational spacesuit
        if ($player->isAlive() && !$player->hasOperationalEquipmentByName(GearItemEnum::SPACESUIT)) {
            $this->playerService->killPlayer(
                player: $player,
                endReason: EndCauseEnum::mapEndCause($tags),
                time: $time
            );
        }
    }
}
