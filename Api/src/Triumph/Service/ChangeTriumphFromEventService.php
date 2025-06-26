<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Event\TriumphChangedEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class ChangeTriumphFromEventService
{
    public function __construct(
        private CycleServiceInterface $cycleService,
        private EventServiceInterface $eventService,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private StatusServiceInterface $statusService,
        private TriumphConfigRepositoryInterface $triumphConfigRepository,
    ) {}

    public function execute(TriumphSourceEventInterface $event): void
    {
        $triumphConfigs = $this->triumphConfigRepository->findAllByTargetedEvent($event);

        foreach ($triumphConfigs as $triumphConfig) {
            $event->getTriumphTargets($triumphConfig)->map(
                fn (Player $player) => $this->addTriumphToPlayer($triumphConfig, $player)
            );
        }
    }

    public function computeNewMushTriumph(Daedalus $daedalus, int $triumphChangePerCycle): int
    {
        $mushInitialBonus = $daedalus->getGameConfig()->getTriumphConfig()->getByNameOrNull(TriumphEnum::MUSH_INITIAL_BONUS);
        $startingTriumph = $mushInitialBonus ? $mushInitialBonus->getQuantity() : 0;
        $filledAt = $daedalus->getFilledAt() ?? new \DateTime();
        $nextCycleAt = $this->cycleService->getDateStartNextCycle($daedalus);
        $cyclesLasted = $this->cycleService->getNumberOfCycleElapsed($filledAt, $nextCycleAt, $daedalus->getDaedalusInfo());

        return max($startingTriumph + $cyclesLasted * $triumphChangePerCycle, 0);
    }

    private function addTriumphToPlayer(TriumphConfig $triumphConfig, Player $player): void
    {
        if ($this->isPreventedByRegression($triumphConfig, $player)) {
            return;
        }

        $quantity = $this->computeTriumphForPlayer($triumphConfig, $player);

        // Don't call triumph changed by 0 event unless the config allows it
        if ($quantity === 0 && !$triumphConfig->shouldRegisterZeroTriumph()) {
            return;
        }

        $player->addTriumph($quantity);
        $this->recordTriumphGain($triumphConfig, $player, $quantity);

        $this->eventService->callEvent(
            new TriumphChangedEvent($player, $triumphConfig, $quantity),
            TriumphChangedEvent::class,
        );
    }

    private function computeTriumphForPlayer(TriumphConfig $triumphConfig, Player $player): int
    {
        return match ($triumphConfig->getName()) {
            TriumphEnum::CYCLE_HUMAN => $player->isActive() ? $triumphConfig->getQuantity() : 0,
            TriumphEnum::CYCLE_MUSH_LATE => $this->computeNewMushTriumph($player->getDaedalus(), $triumphConfig->getQuantity()),
            TriumphEnum::EDEN_ALIEN_PLANT, TriumphEnum::EDEN_ALIEN_PLANT_PLUS => $this->getDifferentAlienPlantCount($player->getDaedalus()) * $triumphConfig->getQuantity(),
            TriumphEnum::EDEN_CAT, TriumphEnum::EDEN_MUSH_CAT, TriumphEnum::EDEN_NO_CAT => $this->checkCatStatus($triumphConfig, $player->getDaedalus()) ? $triumphConfig->getQuantity() : 0,
            TriumphEnum::EDEN_MICROBES => $player->getDaedalus()->getAlivePlayers()->filter(static fn (Player $player) => $player->getMedicalConditions()->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->count() > 0)->count() * $triumphConfig->getQuantity(),
            TriumphEnum::EDEN_MUSH_INTRUDER, TriumphEnum::SOL_MUSH_INTRUDER => $player->getDaedalus()->getMushPlayers()->getPlayerAlive()->count() * $triumphConfig->getQuantity(),
            TriumphEnum::EDEN_ONE_MAN => $player->getDaedalus()->getAlivePlayers()->count() * $triumphConfig->getQuantity(),
            TriumphEnum::EDEN_SEXY => $this->isCrewReproductive($player->getDaedalus()->getAlivePlayers()) ? $triumphConfig->getQuantity() : 0,
            TriumphEnum::PILGRED_MOTHER => $player->getDaedalus()->getProjectByName(ProjectName::PILGRED)->getNumberOfProgressStepsCrossedForThreshold(20) * $triumphConfig->getQuantity(),
            TriumphEnum::PREGNANT_IN_EDEN => $player->getDaedalus()->getAlivePlayers()->filter(static fn (Player $player) => $player->hasStatus(PlayerStatusEnum::PREGNANT))->count() * $triumphConfig->getQuantity(),
            TriumphEnum::RESEARCH_BRILLANT_END => $this->getNumberOfCompletedTriumphResearch(TriumphEnum::RESEARCH_BRILLANT, $player->getDaedalus()) * $triumphConfig->getQuantity(),
            TriumphEnum::RESEARCH_SMALL_END => $this->getNumberOfCompletedTriumphResearch(TriumphEnum::RESEARCH_SMALL, $player->getDaedalus()) * $triumphConfig->getQuantity(),
            TriumphEnum::RESEARCH_STANDARD_END => $this->getNumberOfCompletedTriumphResearch(TriumphEnum::RESEARCH_STANDARD, $player->getDaedalus()) * $triumphConfig->getQuantity(),
            default => $triumphConfig->getQuantity(),
        };
    }

    private function recordTriumphGain(TriumphConfig $triumphConfig, Player $player, int $quantity): void
    {
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        $closedPlayer->recordTriumphGain($triumphConfig->getLogName(), $quantity);
    }

    private function isPreventedByRegression(TriumphConfig $triumphConfig, Player $player): bool
    {
        if (!$triumphConfig->isRegressive()) {
            return false;
        }

        $timesTriumphChanged = $this->statusService->createOrIncrementChargeStatus(
            name: PlayerStatusEnum::PERSONAL_TRIUMPH_REGRESSION,
            holder: $player
        )->getCharge();
        $divisor = 1 + (int) ($timesTriumphChanged / $triumphConfig->getRegressiveFactor());

        return $timesTriumphChanged % $divisor !== 0;
    }

    private function getNumberOfCompletedTriumphResearch(TriumphEnum $triumphName, Daedalus $daedalus): int
    {
        $config = $daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow($triumphName);
        $researchNames = array_keys($config->getTagConstraints());

        return $daedalus->getFinishedResearchProjects()->filter(static fn (Project $research) => \in_array($research->getName(), $researchNames, true))->count();
    }

    private function checkCatStatus(TriumphConfig $triumphConfig, Daedalus $daedalus): bool
    {
        $cats = $this->gameEquipmentRepository->findByNameAndDaedalus(ItemEnum::SCHRODINGER, $daedalus);

        // Check if there are no cats existing.
        if (\count($cats) === 0) {
            return $triumphConfig->getName() === TriumphEnum::EDEN_NO_CAT;
        }

        // Else, check if there are any infected cats.
        if (\count(array_filter($cats, static fn (GameItem $cat) => $cat->hasStatus(EquipmentStatusEnum::CAT_INFECTED))) > 0) {
            return $triumphConfig->getName() === TriumphEnum::EDEN_MUSH_CAT;
        }

        return $triumphConfig->getName() === TriumphEnum::EDEN_CAT;
    }

    private function getDifferentAlienPlantCount(Daedalus $daedalus): int
    {
        $searchedItemNames = GamePlantEnum::getAlienPlants();
        $existingAlienPlants = $this->gameEquipmentRepository->findByNamesAndDaedalus($searchedItemNames, $daedalus);
        $existingAlienPlantNames = array_map(static fn (GameEquipment $plant) => $plant->getName(), $existingAlienPlants);

        return \count(array_unique($existingAlienPlantNames));
    }

    private function isCrewReproductive(PlayerCollection $crew): bool
    {
        return $crew->getMalePlayers()->count() > 0 && $crew->getMalePlayers()->count() < $crew->count();
    }
}
