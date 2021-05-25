<?php

namespace Mush\Disease\Service;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private PlayerDiseaseService $playerDiseaseService;

    public function __construct(
        PlayerDiseaseService $playerDiseaseService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
    }

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void
    {
        if (!$gameEquipment->getStatuses()->filter(
            fn (Status $status) => in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::HAZARDOUS, EquipmentStatusEnum::UNSTABLE])
        )->isEmpty()
        ) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::SPOILED_FOOD, $player);
        }
    }
}
