<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const HAZARDOUS_RATE = 30;
    private const DECOMPOSING_RATE = 50;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        EquipmentEffectServiceInterface $equipmentEffectService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->equipmentEffectService = $equipmentEffectService;
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

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void
    {
        $rationType = $gameEquipment->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        $consumableEffect = $this->equipmentEffectService->getConsumableEffect($gameEquipment->getName(), $rationType, $player->getDaedalus());

        /** @var ConsumableDiseaseAttribute $disease */
        foreach ($consumableEffect->getDiseases() as $disease) {
            if ($this->randomService->isSuccessful($disease->getRate())) {
                $this->playerDiseaseService->createDiseaseFromName($disease->getDisease(), $player);
            }
        }

        foreach ($consumableEffect->getDisorder() as $disease) {
            if ($this->randomService->isSuccessful($disease->getRate())) {
                $this->playerDiseaseService->createDiseaseFromName($disease->getDisease(), $player);
            }
        }

        /** @var ConsumableDiseaseAttribute $cure */
        foreach ($consumableEffect->getCures() as $cure) {
            if (($disease = $player->getDiseaseByName($cure->getDisease())) !== null &&
                $this->randomService->isSuccessful($cure->getRate())
            ) {
                $this->playerDiseaseService->removePlayerDisease($disease, new \DateTime());
            }
        }
    }
}
