<?php

namespace Mush\Player\Listener;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentSubscriber implements EventSubscriberInterface
{
    // A player variable event cannot change sign through modifiers, so we need to use a tiny negative value to make
    // cat owner lose morale
    public const float GLOBAL_MORALE_LOSS_SCHRODINGER_DEATH = -PHP_FLOAT_EPSILON;

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
        $this->ejectPlayersFromPatrolship($event);

        $this->handleSchrodingerDeathMoraleLoss($event);
    }

    private function ejectPlayersFromPatrolship(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $equipmentPlace = $event->getPlace();

        if ($equipment->isAMonoplaceShip()) {
            foreach ($equipmentPlace->getPlayers() as $player) {
                $this->ejectPlayer($player, $event->getTags(), $event->getTime());
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

    private function handleSchrodingerDeathMoraleLoss(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment->isSchrodinger()) {
            $alivePlayers = $event->getDaedalus()->getAlivePlayers();
            foreach ($alivePlayers as $player) {
                $playerVariableEvent = new PlayerVariableEvent(
                    $player,
                    PlayerVariableEnum::MORAL_POINT,
                    self::GLOBAL_MORALE_LOSS_SCHRODINGER_DEATH,
                    $event->getTags(),
                    new \DateTime(),
                );
                $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
            }
        }
    }
}
