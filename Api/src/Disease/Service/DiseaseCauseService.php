<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const HAZARDOUS_RATE = 50;
    private const DECOMPOSING_RATE = 90;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->consumableDiseaseService = $consumableDiseaseService;
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
        $consumableEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $player->getDaedalus());

        if ($consumableEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableEffect->getDiseases() as $disease) {
                if ($this->randomService->isSuccessful($disease->getRate())) {
                    $this->playerDiseaseService->createDiseaseFromName(
                        $disease->getDisease(),
                        $player,
                        DiseaseCauseEnum::CONSUMABLE_EFFECT,
                        $disease->getDelayMin(),
                        $disease->getDelayLength()
                    );
                }
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableEffect->getCures() as $cure) {
                if (($disease = $player->getMedicalConditionByName($cure->getDisease())) !== null &&
                    $this->randomService->isSuccessful($cure->getRate())
                ) {
                    $this->playerDiseaseService->removePlayerDisease($disease, DiseaseStatusEnum::DRUG_HEALED, new \DateTime(), VisibilityEnum::PRIVATE);
                }
            }
        }
    }
}
