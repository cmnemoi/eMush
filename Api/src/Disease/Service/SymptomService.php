<?php

namespace Mush\Disease\Service;

use Mush\Action\Actions\Attack;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\Shoot;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymptomService implements SymptomServiceInterface
{
    private ActionServiceInterface $actionService;
    private EventServiceInterface $eventService;
    private EventModifierServiceInterface $modifierService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private DiseaseCauseServiceInterface $diseaseCauseService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private ValidatorInterface $validator;

    public function __construct(
        ActionServiceInterface $actionService,
        EventServiceInterface $eventService,
        EventModifierServiceInterface $modifierService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        ValidatorInterface $validator,
    ) {
        $this->actionService = $actionService;
        $this->eventService = $eventService;
        $this->modifierService = $modifierService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->diseaseCauseService = $diseaseCauseService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->validator = $validator;
    }

    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        switch ($symptomConfig->getSymptomName()) {
            case SymptomEnum::BITING:
                $this->handleBiting($symptomConfig, $player, $time);
                break;
            case SymptomEnum::DIRTINESS:
                $this->handleDirtiness($symptomConfig, $player, $time);
                break;
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown cycle change symptom');
        }
    }

    public function handleStatusAppliedSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        switch ($symptomConfig->getSymptomName()) {
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown status applied symptom');
        }
    }

    private function handleBiting(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['target_character'] = $playerToBite->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            [$symptomConfig->getSymptomName()],
            $time
        );

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    public function handleBreakouts(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::BREAKOUTS) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleCatAllergy(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::CAT_ALLERGY) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['character_gender'] = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $damageEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -6,
            [$symptomConfig->getSymptomName()],
            $time
        );

        $this->eventService->callEvent($damageEvent, VariableEventInterface::CHANGE_VARIABLE);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, [$symptomConfig->getSymptomName()]);

        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            [$symptomConfig->getSymptomName()],
            $time
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }

    private function handleDirtiness(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, [$symptomConfig->getSymptomName()], $time);
        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleDirty(Player $player, array $reasons, \DateTime $time): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $reasons,
            $time
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    public function handleDrooling(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::DROOLING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleFearOfCats(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::FEAR_OF_CATS) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $this->makePlayerRandomlyMoving($player);

        $this->createSymptomLog($symptomConfig->getSymptomName() . '_notif', $player, $time, VisibilityEnum::PRIVATE, $logParameters);
    }

    public function handleFoamingMouth(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::FOAMING_MOUTH) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handlePsychoticAttacks(SymptomConfig $symptomConfig, Player $player): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::PSYCHOTIC_ATTACKS) {
            return;
        }

        $this->makePlayerRandomlyAttacking($player);
        $this->makePlayerRandomlyShooting($player);
    }

    public function handleSneezing(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::SNEEZING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    public function handleSepticemia(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::SEPTICEMIA) {
            return;
        }

        if (!$player->isAlive()) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $player,
            [EndCauseEnum::INFECTION],
            $time
        );

        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
    }

    public function handleVomiting(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        if ($symptomConfig->getSymptomName() !== SymptomEnum::VOMITING) {
            return;
        }

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, [$symptomConfig->getSymptomName()], $time);
        $this->createSymptomLog($symptomConfig->getSymptomName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function createSymptomLog(string $symptomLogKey,
        Player $player,
        \DateTime $date,
        string $visibility = VisibilityEnum::PUBLIC,
        array $logParameters = []): void
    {
        $this->roomLogService->createLog(
            $symptomLogKey,
            $player->getPlace(),
            $visibility,
            'event_log',
            $player,
            $logParameters,
            $date
        );
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

        return reset($draw);
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

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): void
    {
        // get non-broken doors
        $availableDoors = $player->getPlace()->getDoors()->filter(function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        if (count($availableDoors) === 0) {
            return;
        }

        // get random door
        $selectedDoor = $this->randomService->getRandomElements($availableDoors, 1);
        $randomDoor = reset($selectedDoor);

        /** @var Action $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions()->filter(function (Action $action) {
            return $action->getActionName() === ActionEnum::MOVE;
        })->first();

        $moveAction = new Move(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService
        );
        $moveAction->loadParameters($moveActionEntity, $player, $randomDoor);
        $moveAction->execute();
    }
}
