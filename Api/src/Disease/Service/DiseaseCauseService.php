<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private PlayerDiseaseService $playerDiseaseService;
    private DiseaseConfigRepository $diseaseConfigRepository;

    public function __construct(
        PlayerDiseaseService $playerDiseaseService,
        DiseaseConfigRepository $diseaseConfigRepository
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
    }

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): ?PlayerDisease
    {
        if ($gameEquipment->getStatuses()->filter(
            fn (Status $status) => in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::HAZARDOUS, EquipmentStatusEnum::UNSTABLE])
        )
        ) {
            $diseaseConfigs = $this->diseaseConfigRepository->findByCauses(DiseaseCauseEnum::SPOILED_FOOD, $player->getDaedalus());
            foreach ($diseaseConfigs as $diseaseConfig) {
                $disease = new PlayerDisease();
                $disease
                    ->setPlayer($player)
                    ->setDiseaseConfig($diseaseConfig)
                    ->setDiseasePoint(10)
                ;
                $this->playerDiseaseService->persist($disease);
            }
        }
    }
}
