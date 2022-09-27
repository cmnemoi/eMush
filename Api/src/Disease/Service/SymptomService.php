<?php

namespace Mush\Disease\Service;

use DateTime;
use Mush\Action\Actions\Hit;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
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
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private ValidatorInterface $validator;

    public function __construct(
        ActionServiceInterface $actionService,
        EventServiceInterface $eventService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        ValidatorInterface $validator,
    ) {
        $this->actionService = $actionService;
          $this->eventService = $eventService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->validator = $validator;
    }

    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::BITING:
                $this->handleBiting($symptomConfig, $player, $time);
                break;
            case SymptomEnum::DIRTINESS:
                $this->handleDirtiness($symptomConfig, $player, $time);
                break;
            case SymptomEnum::PSYCHOTIC_ATTACKS:
                $this->handlePsychoticAttacks($symptomConfig, $player, $time);
                break;
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($player, $time);
                break;
            default:
                throw new \Exception('Unknown cycle change symptom');
        }
    }

    public function handlePostActionSymptom(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::BREAKOUTS:
                $this->handleBreakouts($symptomConfig, $player, $time);
                break;
            case SymptomEnum::CAT_ALLERGY:
                $this->handleCatAllergy($symptomConfig, $player, $time);
                break;
            case SymptomEnum::DROOLING:
                $this->handleDrooling($symptomConfig, $player, $time);
                break;
            case SymptomEnum::FEAR_OF_CATS:
                $this->handleFearOfCats($symptomConfig, $player, $time);
                break;
            case SymptomEnum::FOAMING_MOUTH:
                $this->handleFoamingMouth($symptomConfig, $player, $time);
                break;
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($player, $time);
                break;
            case SymptomEnum::SNEEZING:
                $this->handleSneezing($symptomConfig, $player, $time);
                break;
            case SymptomEnum::VOMITING:
                $this->handleVomiting($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown post action symptom');
        }
    }

    public function handleStatusAppliedSymptom(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::SEPTICEMIA:
                $this->handleSepticemia($player, $time);
                break;
            default:
                throw new \Exception('Unknown status applied symptom');
        }
    }

    private function createSymptomLog(string $symptomLogKey,
        Player $player,
        DateTime $date,
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

    private function handleBiting(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['target_character'] = $playerToBite->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            $symptomConfig->getName(),
            $time
        );

        $this->eventService->callEvent($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    private function handleBreakouts(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleCatAllergy(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['character_gender'] = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, $symptomConfig->getName());

        $diseaseEvent = new ApplyEffectEvent(
            $player,
            $player,
            VisibilityEnum::PRIVATE,
            $symptomConfig->getName(),
            $time
        );
        $this->eventService->callEvent($diseaseEvent, ApplyEffectEvent::PLAYER_GET_SICK);
    }

    private function handleDirtiness(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleDirty(Player $player, string $reason, DateTime $time): void
    {
        if ($player->hasStatus(PlayerStatusEnum::DIRTY)) {
            return;
        }

        $statusEvent = new StatusEvent(
            PlayerStatusEnum::DIRTY,
            $player,
            $reason,
            $time
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function handleDrooling(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleFearOfCats(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $this->makePlayerRandomlyMoving($player);

        $this->createSymptomLog($symptomConfig->getName() . '_notif', $player, $time, VisibilityEnum::PRIVATE, $logParameters);
    }

    private function handleFoamingMouth(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handlePsychoticAttacks(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $this->makePlayerRandomlyHitting($player);
    }

    private function handleSneezing(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleSepticemia(Player $player, DateTime $time): void
    {
        if (!$player->isAlive()) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $player,
            EndCauseEnum::INFECTION,
            $time
        );

        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function handleVomiting(SymptomConfig $symptomConfig, Player $player, DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    /**
     * This function takes a Player, draws a random player in its room and makes them attack the selected player.
     * If the room is empty, does nothing.
     */
    private function makePlayerRandomlyHitting(Player $player): void
    {
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->getPlayerAlive()->filter(function (Player $p) use ($player) {
            return $p !== $player;
        })->toArray();

        if (count($otherPlayersInRoom) === 0) {
            return;
        }

        $draw = $this->randomService->getRandomElements($otherPlayersInRoom, 1);
        $victim = reset($draw);

        /** @var Action $hitActionEntity */
        $hitActionEntity = $player->getTargetActions()->filter(function (Action $action) {
            return $action->getName() === ActionEnum::HIT;
        })->first();

        if ($hitActionEntity === null) {
            throw new \Exception('Player should have a Hit action');
        }

        $hitAction = new Hit(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->randomService
        );

        $hitAction->loadParameters($hitActionEntity, $player, $victim);
        $hitAction->execute();
    }

    /**
     * This function takes a player as an argument, draws a random room and make them move to it.
     */
    private function makePlayerRandomlyMoving(Player $player): void
    {
        // get non broken doors
        $availaibleDoors = $player->getPlace()->getDoors()->filter(function (GameEquipment $door) {
            return !$door->isBroken();
        })->toArray();

        // get random door
        $selectedDoor = $this->randomService->getRandomElements($availaibleDoors, 1);
        $randomDoor = reset($selectedDoor);

        /** @var Action $moveActionEntity */
        $moveActionEntity = $randomDoor->getActions()->filter(function (Action $action) {
            return $action->getName() === ActionEnum::MOVE;
        })->first();

        $moveAction = new Move(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerService
        );
        $moveAction->loadParameters($moveActionEntity, $player, $randomDoor);
        $moveAction->execute();
    }
}
