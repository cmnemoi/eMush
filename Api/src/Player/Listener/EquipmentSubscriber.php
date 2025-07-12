<?php

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentSubscriber implements EventSubscriberInterface
{
    // A player variable event cannot change sign through modifiers, so we need to use a tiny negative value to make
    // cat owner lose morale
    public const float GLOBAL_MORALE_LOSS_SCHRODINGER_DEATH = -PHP_FLOAT_EPSILON;

    public function __construct(
        private EventServiceInterface $eventService,
        private PlayerRepositoryInterface $playerRepository,
        private PlayerServiceInterface $playerService
    ) {}

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

        $this->createShootCatAuthorHighlight($event);
    }

    private function ejectPlayersFromPatrolship(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $equipmentPlace = $event->getPlace();

        if ($equipmentPlace->getType() !== PlaceTypeEnum::PATROL_SHIP) {
            return;
        }

        if ($equipment instanceof SpaceShip) {
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

    private function createShootCatAuthorHighlight(EquipmentEvent $event): void
    {
        if ($event->doesNotHaveTag(ActionEnum::SHOOT_CAT->toString())) {
            return;
        }

        $author = $event->getAuthorOrThrow();
        $author->addPlayerHighlight(PlayerHighlight::fromEventForAuthor($event));
        $this->playerRepository->save($author);
    }
}
