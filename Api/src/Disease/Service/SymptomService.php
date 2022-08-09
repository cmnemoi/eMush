<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SymptomService implements SymptomServiceInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        switch ($symptomConfig->getName()) {
            case SymptomEnum::BITING:
                $this->handleBiting($symptomConfig, $player, $time);
                break;
            case SymptomEnum::DIRTINESS:
                $this->handleDirtiness($symptomConfig, $player, $time);
                break;
            default:
                throw new \Exception('Unknown cycle change symptom');
        }
    }

    public function handlePostActionSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
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
            case SymptomEnum::FOAMING_MOUTH:
                $this->handleFoamingMouth($symptomConfig, $player, $time);
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

    private function handleBiting(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
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

        $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    private function handleBreakouts(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleCatAllergy(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();
        $logParameters['character_gender'] = CharacterEnum::isMale($player->getName()) ? 'male' : 'female';

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);

        $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::QUINCKS_OEDEMA, $player, $symptomConfig->getName());

        // @TO DO: apply injury to player
        // $injuries = [InjuryEnum::BURNT_ARMS, InjuryEnum::BURNT_HAND];
        // $selectedInjury = array_values($this->randomService->getRandomElements($injuries))[0];

        // $this->playerDiseaseService->createDiseaseFromName($selectedInjury, $player, $symptomConfig->getName());
    }

    private function handleDirtiness(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleDirty(Player $player, string $reason, \DateTime $time): void
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

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function handleDrooling(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleFoamingMouth(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleSneezing(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }

    private function handleVomiting(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void
    {
        $logParameters = [];
        $logParameters[$player->getLogKey()] = $player->getLogName();

        $this->handleDirty($player, $symptomConfig->getName(), $time);
        $this->createSymptomLog($symptomConfig->getName(), $player, $time, $symptomConfig->getVisibility(), $logParameters);
    }
}
