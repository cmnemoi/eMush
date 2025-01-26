<?php

namespace Mush\Disease\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\ProbaCollectionRandomElementServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

final readonly class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const int HAZARDOUS_RATE = 50;
    private const int DECOMPOSING_RATE = 90;

    public function __construct(
        private ConsumableDiseaseServiceInterface $consumableDiseaseService,
        private D100RollServiceInterface $d100Roll,
        private ProbaCollectionRandomElementServiceInterface $probaCollectionRandomElement,
        private PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {}

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void
    {
        if ($this->foodShouldMakePlayerSick($gameEquipment)) {
            $this->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
        }
    }

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void
    {
        $consumableEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $player->getDaedalus());

        if ($consumableEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableEffect->getDiseases() as $disease) {
                if ($this->d100Roll->isSuccessful($disease->getRate())) {
                    $this->playerDiseaseService->createDiseaseFromName(
                        $disease->getDisease(),
                        $player,
                        [DiseaseCauseEnum::CONSUMABLE_EFFECT],
                        $disease->getDelayMin(),
                        $disease->getDelayLength()
                    );
                }
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableEffect->getCures() as $cure) {
                $disease = $player->getMedicalConditionByName($cure->getDisease());
                if ($disease?->isActive() && $this->d100Roll->isSuccessful($cure->getRate())) {
                    $this->playerDiseaseService->removePlayerDisease($disease, [DiseaseStatusEnum::DRUG_HEALED], new \DateTime(), VisibilityEnum::PUBLIC);
                }
            }
        }
    }

    public function findCauseConfigByDaedalus(string $causeName, Daedalus $daedalus): DiseaseCauseConfig
    {
        $causesConfigs = $daedalus->getGameConfig()->getDiseaseCauseConfig()->filter(static fn (DiseaseCauseConfig $causeConfig) => $causeConfig->getCauseName() === $causeName);

        if ($causesConfigs->count() !== 1) {
            throw new \Exception("there should be exactly 1 diseaseCauseConfig for this cause ({$causeName}).");
        }

        return $causesConfigs->first();
    }

    public function handleDiseaseForCause(string $cause, Player $player, ?int $delayMin = null, ?int $delayLength = null): PlayerDisease
    {
        $diseasesProbaCollection = $this->findCauseConfigByDaedalus($cause, $player->getDaedalus())->getDiseases();

        $diseaseName = (string) $this->probaCollectionRandomElement->generateFrom($diseasesProbaCollection);

        return $this->playerDiseaseService->createDiseaseFromName($diseaseName, $player, [$cause], $delayMin, $delayLength);
    }

    public function foodShouldMakePlayerSick(GameEquipment $gameEquipment): bool
    {
        return $this->hazardousFoodShouldMakePlayerSick($gameEquipment) || $this->decomposingFoodShouldMakePlayerSick($gameEquipment);
    }

    public function hazardousFoodShouldMakePlayerSick(GameEquipment $gameEquipment): bool
    {
        return $gameEquipment->hasStatus(EquipmentStatusEnum::HAZARDOUS) && $this->d100Roll->isSuccessful(self::HAZARDOUS_RATE);
    }

    public function decomposingFoodShouldMakePlayerSick(GameEquipment $gameEquipment): bool
    {
        return $gameEquipment->hasStatus(EquipmentStatusEnum::DECOMPOSING) && $this->d100Roll->isSuccessful(self::DECOMPOSING_RATE);
    }
}
