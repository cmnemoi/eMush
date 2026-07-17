<?php

declare(strict_types=1);

namespace Mush\Modifier\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\Hunter;
use Mush\Modifier\Service\ModifierListenerService\CommunicationModifierService;
use Mush\Modifier\Service\ModifierListenerService\DiseaseModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\ProjectModifierService;
use Mush\Modifier\Service\ModifierListenerService\SkillModifierService;
use Mush\Modifier\Service\ModifierListenerService\StatusModifierService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;

class ModifierDeleteByDaedalusService
{
    public function __construct(
        private SkillModifierService $skillModifierService,
        private StatusModifierService $statusModifierService,
        private EquipmentModifierServiceInterface $equipmentModifierService,
        private DiseaseModifierServiceInterface $diseaseModifierService,
        private ProjectModifierService $projectModifierService,
        private CommunicationModifierService $communicationModifierService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private XylophRepositoryInterface $xylophRepository,
    ) {}

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function execute(Daedalus $daedalus): void
    {
        // players
        foreach ($daedalus->getPlayers() as $player) {
            $this->removeForPlayer($player);
        }
        // status
        foreach ($daedalus->getStatuses() as $status) {
            $this->statusModifierService->deleteStatusModifiers($status);
        }
        // places
        foreach ($daedalus->getPlaces() as $place) {
            $this->removeForPlace($place);
        }
        // hunters
        foreach ($daedalus->getHuntersAroundDaedalus() as $hunter) {
            if ($hunter instanceof Hunter) {
                $this->removeForHunter($hunter);
            }
        }
        // project
        foreach ($daedalus->getAllFinishedProjects() as $project) {
            if ($project instanceof Project) {
                $this->projectModifierService->deleteProjectModifiers($project);
            }
        }
        // rebel base
        foreach ($this->rebelBaseRepository->findAllDecodedRebelBases($daedalus->getId()) as $rebelBase) {
            if ($rebelBase instanceof RebelBase) {
                $this->communicationModifierService->deleteRebelBaseModifiers($rebelBase, $daedalus);
            }
        }
        // xyloph entry
        foreach ($this->xylophRepository->findAllDecodedByDaedalusId($daedalus->getId()) as $xyloph) {
            if ($xyloph instanceof XylophEntry) {
                $this->communicationModifierService->deleteXylophModifiers($xyloph, $daedalus);
            }
        }
    }

    private function removeForPlayer(Player $player): void
    {
        // skills
        foreach ($player->getSkills() as $skill) {
            $this->skillModifierService->deleteSkillModifiers($skill);
        }

        // status
        foreach ($player->getStatuses() as $status) {
            $this->statusModifierService->deleteStatusModifiers($status);
        }
        // equipment
        foreach ($player->getEquipments() as $equipment) {
            $this->removeForEquipment($equipment);
        }
        // diseases
        foreach ($player->getMedicalConditions() as $disease) {
            if ($disease instanceof PlayerDisease) {
                $this->diseaseModifierService->cureDisease($player, $disease, ['MIGRATION'], new \DateTime());
            }
        }
    }

    private function removeForEquipment(GameEquipment $equipment): void
    {
        // status
        foreach ($equipment->getStatuses() as $status) {
            $this->statusModifierService->deleteStatusModifiers($status);
        }

        // gear
        $this->equipmentModifierService->gearDestroyed($equipment, ['MIGRATION'], new \DateTime());
    }

    private function removeForPlace(Place $Place): void
    {
        // status
        foreach ($Place->getStatuses() as $status) {
            $this->statusModifierService->deleteStatusModifiers($status);
        }

        // equipment
        foreach ($Place->getEquipments() as $equipment) {
            $this->removeForEquipment($equipment);
        }
    }

    private function removeForHunter(Hunter $hunter): void
    {
        // status
        foreach ($hunter->getStatuses() as $status) {
            $this->statusModifierService->deleteStatusModifiers($status);
        }
    }
}
