<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Actions\Attack;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class PsychoticAttack extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::PSYCHOTIC_ATTACKS;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService
    ) {
        $this->randomService = $randomService;
        $this->eventService = $eventService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): void {
        $attackEvent = $this->makePlayerRandomlyAttacking($player);
        $shootEvent = $this->makePlayerRandomlyShooting($player);

        // check if those events are possible. If both, randomly pick one
        if ($attackEvent === null && $shootEvent === null) {
            return;
        }
        if (
            $attackEvent !== null
            && ($shootEvent === null || $this->randomService->isSuccessful(50))
        ) {
            $this->eventService->callEvent($attackEvent, $attackEvent->getEventName());
        } else {
            $this->eventService->callEvent($shootEvent, $shootEvent->getEventName());
        }
    }

    private function drawRandomPlayerInRoom(Player $player): ?Player
    {
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive()->filter(static function (Player $p) use ($player) {
            return $p !== $player;
        })->toArray();

        if (\count($otherPlayersInRoom) === 0) {
            return null;
        }

        $draw = $this->randomService->getRandomElements($otherPlayersInRoom, 1);

        return reset($draw);
    }

    private function getPlayerWeapon(Player $player, string $weapon): ?EquipmentConfig
    {
        $weapon = $player->getEquipments()->filter(
            static fn (GameItem $gameItem) => $gameItem->getName() === $weapon && $gameItem->isOperational()
        )->first();

        return $weapon ? $weapon->getEquipment() : null;
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyAttacking(Player $player): ?ActionEvent
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return null;
        }

        $knife = $this->getPlayerWeapon($player, ItemEnum::KNIFE);
        if ($knife === null) {
            return null;
        }

        /** @var Action $attackActionEntity */
        $attackActionEntity = $knife->getActions()->filter(
            static fn (Action $action) => $action->getActionName() === ActionEnum::ATTACK
        )->first();

        if (!$attackActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyAttacking() : Player ' . $player->getName() . ' should have a Attack action');
        }

        $actionEvent = new ActionEvent($attackActionEntity, $player, $victim);
        $actionEvent->setEventName(ActionEvent::EXECUTE_ACTION);

        return $actionEvent;
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyShooting(Player $player): ?ActionEvent
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return null;
        }

        $blaster = $this->getPlayerWeapon($player, ItemEnum::BLASTER);
        if ($blaster === null) {
            return null;
        }

        /** @var Action $shootActionEntity */
        $shootActionEntity = $blaster->getActions()->filter(
            static fn (Action $action) => $action->getActionName() === ActionEnum::SHOOT
        )->first();

        if (!$shootActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyShooting() : Player' . $player->getName() . 'should have a Shoot action');
        }

        $actionEvent = new ActionEvent($shootActionEntity, $player, $victim);
        $actionEvent->setEventName(ActionEvent::EXECUTE_ACTION);

        return $actionEvent;
    }
}
