<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Action\Actions\Attack;
use Mush\Action\Actions\Shoot;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PsychoticAttack extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::PSYCHOTIC_ATTACKS;
    private ActionServiceInterface $actionService;
    private EventServiceInterface $eventService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private RandomServiceInterface $randomService;
    private ValidatorInterface $validator;

    public function __construct(
        ActionServiceInterface $actionService,
        EventServiceInterface $eventService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        RandomServiceInterface $randomService,
        ValidatorInterface $validator,
    ) {
        $this->actionService = $actionService;
        $this->eventService = $eventService;
        $this->diseaseCauseService = $diseaseCauseService;
        $this->randomService = $randomService;
        $this->validator = $validator;
    }

    public function applyEffects(string $symptomName, Player $player, \DateTime $time): void
    {
        if ($symptomName !== SymptomEnum::PSYCHOTIC_ATTACKS) {
            return;
        }

        $this->makePlayerRandomlyAttacking($player);
        $this->makePlayerRandomlyShooting($player);
    }

    private function drawRandomPlayerInRoom(Player $player): ?Player
    {
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive()->filter(function (Player $p) use ($player) {
            return $p !== $player;
        })->toArray();

        if (count($otherPlayersInRoom) === 0) {
            return null;
        }

        $draw = $this->randomService->getRandomElements($otherPlayersInRoom, 1);
        $drawnPlayer = reset($draw);

        return $drawnPlayer;
    }

    private function getPlayerWeapon(Player $player, string $weapon): ?EquipmentConfig
    {
        $weapon = $player->getEquipments()->filter(
            fn (GameItem $gameItem) => $gameItem->getName() === $weapon && $gameItem->isOperational()
        )->first();

        return $weapon ? $weapon->getEquipment() : null;
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyAttacking(Player $player): void
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return;
        }

        $knife = $this->getPlayerWeapon($player, ItemEnum::KNIFE);
        if ($knife === null) {
            return;
        }

        /** @var Action $attackActionEntity */
        $attackActionEntity = $knife->getActions()->filter(
            fn (Action $action) => $action->getActionName() === ActionEnum::ATTACK
        )->first();

        if (!$attackActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyAttacking() : Player ' . $player->getName() . ' should have a Attack action');
        }

        $attackAction = new Attack(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->diseaseCauseService
        );

        $attackAction->loadParameters($attackActionEntity, $player, $victim);
        $attackAction->execute();
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty or if player doesn't have a knife, does nothing.
     */
    private function makePlayerRandomlyShooting(Player $player): void
    {
        $victim = $this->drawRandomPlayerInRoom($player);
        if ($victim === null) {
            return;
        }

        $blaster = $this->getPlayerWeapon($player, ItemEnum::BLASTER);
        if ($blaster === null) {
            return;
        }

        /** @var Action $shootActionEntity */
        $shootActionEntity = $blaster->getActions()->filter(
            fn (Action $action) => $action->getActionName() === ActionEnum::SHOOT
        )->first();

        if (!$shootActionEntity instanceof Action) {
            throw new \Exception('makePlayerRandomlyShooting() : Player' . $player->getName() . 'should have a Shoot action');
        }

        $shootAction = new Shoot(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->diseaseCauseService
        );

        $shootAction->loadParameters($shootActionEntity, $player, $victim);
        $shootAction->execute();
    }
}
