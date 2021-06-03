<?php

namespace Mush\Disease\Service;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const HAZARDOUS_RATE = 30;
    private const DECOMPOSING_RATE = 50;

    private PlayerDiseaseService $playerDiseaseService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerDiseaseService $playerDiseaseService,
        RandomServiceInterface $randomService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
    }

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void
    {
        if (($gameEquipment->hasStatus(EquipmentStatusEnum::HAZARDOUS) &&
                $this->randomService->isSuccessful(self::HAZARDOUS_RATE))
            || ($gameEquipment->hasStatus(EquipmentStatusEnum::DECOMPOSING) &&
                $this->randomService->isSuccessful(self::DECOMPOSING_RATE))
        ) {
            $this->playerDiseaseService->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
        }
    }
}
