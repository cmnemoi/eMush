<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\GameEquipment;
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
        $possibleEvents = [];

        if ($attackEvent = $this->createViolentActionEvent($player, ItemEnum::KNIFE, ActionEnum::ATTACK->value)) {
            $possibleEvents[] = $attackEvent;
        }

        if ($shootEvent = $this->createViolentActionEvent($player, ItemEnum::BLASTER, ActionEnum::SHOOT->value)) {
            $possibleEvents[] = $shootEvent;
        }

        if (\count($possibleEvents) === 0) {
            return;
        }

        $eventToTrigger = $possibleEvents[array_rand($possibleEvents)];

        $this->eventService->callEvent($eventToTrigger, $eventToTrigger->getEventName());
    }

    private function createViolentActionEvent(Player $player, string $weaponName, string $actionName): ?ActionEvent
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return null;
        }

        $weapon = $this->getPlayerWeapon($player, $weaponName);
        if ($weapon === null) {
            return null;
        }

        /** @var ActionConfig $actionConfig */
        $actionConfig = $weapon->getEquipment()->getActionConfigs()->filter(
            static fn (ActionConfig $action) => $action->getActionName()->toString() === $actionName
        )->first();

        if (!$actionConfig instanceof ActionConfig) {
            throw new \Exception("Player {$player->getName()} with weapon {$weaponName} should have a {$actionName} action");
        }

        $tags = $actionConfig->getActionTags();
        $tags[] = $this->name;

        $actionEvent = new ActionEvent(
            actionConfig: $actionConfig,
            actionProvider: $weapon,
            player: $player,
            tags: $tags,
            actionTarget: $victim
        );
        $actionEvent->setEventName(ActionEvent::EXECUTE_ACTION);

        return $actionEvent;
    }

    private function drawRandomPlayerInRoom(Player $player): ?Player
    {
        $otherPlayersInRoom = $player->getPlace()->getAlivePlayers()->getAllExcept($player);
        if ($otherPlayersInRoom->isEmpty()) {
            return null;
        }

        return $this->randomService->getRandomPlayer($otherPlayersInRoom);
    }

    private function getPlayerWeapon(Player $player, string $weapon): ?GameEquipment
    {
        $weapon = $player->getEquipments()->filter(
            static fn (GameItem $gameItem) => $gameItem->getName() === $weapon && $gameItem->isOperational()
        )->first();

        if ($weapon instanceof GameEquipment) {
            return $weapon;
        }

        return null;
    }
}
