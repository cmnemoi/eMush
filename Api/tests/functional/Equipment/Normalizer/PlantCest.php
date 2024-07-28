<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PlantCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private GameItem $bananaTree;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->bananaTree = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotDisplayEffectsToNonBotanist(FunctionalTester $I): void
    {
        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEmpty($normalizedBananaTree['effects']);
    }

    public function shouldDisplayMaturePlantEffectsToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlantIsMature();

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 1 / jour',
                    'Productivité O2 : 1 / jour',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayThirstyPlantEffectsToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlantIsMature();
        $this->givenPlantIsThirsty();

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 1 / jour',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayDiseasedPlantEffectsToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlantIsMature();
        $this->givenPlantIsDiseased();

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayYoungPlantEffectsToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                    'Ce plant arrivera à maturité dans 36 cycles',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayYoungPlantEffectsToBotanistAfterAFewCyclesOfMaturity(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenPlantAgeIs(10);

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                    'Ce plant arrivera à maturité dans 26 cycles',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayYoungPlantEffectsToBotanistWithHydroponicIncubator(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenHydroponicIncubatorIsInPlayerRoom($I);

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                    'Ce plant arrivera à maturité dans 18 cycles',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldDisplayYoungPlantEffectsToBotanistWithParasiteElimProject(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenParasiteElimProjectIsFinished($I);
        $this->givenPlayerIsInGarden($I);

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                    'Ce plant arrivera à maturité dans 32 cycles',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    public function shouldNotDisplayNegativeMaturationTimeWithParasiteElimProject(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $this->givenParasiteElimProjectIsFinished($I);
        $this->givenPlantAgeIs(35);
        $this->givenPlayerIsInGarden($I);

        $normalizedBananaTree = $this->equipmentNormalizer->normalize(
            $this->bananaTree,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur la plante :',
                'effects' => [
                    'Productivité Fruit : 0 / jour',
                    'Productivité O2 : 0 / jour',
                    'Ce plant arrivera à maturité dans 0 cycles',
                ],
            ],
            actual: $normalizedBananaTree['effects']
        );
    }

    private function givenPlayerIsABotanist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::BOTANIST, $this->player));
    }

    private function givenPlantIsMature(): void
    {
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::PLANT_YOUNG,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlantIsThirsty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_THIRSTY,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlantIsDiseased(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PLANT_DISEASED,
            holder: $this->bananaTree,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlantAgeIs(int $age): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $this->bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG),
            delta: $age,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenHydroponicIncubatorIsInPlayerRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::HYDROPONIC_INCUBATOR,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenParasiteElimProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PARASITE_ELIM),
            author: $this->player,
            I: $I,
        );
    }

    private function givenPlayerIsInGarden(FunctionalTester $I): void
    {
        $garden = $this->createExtraPlace(
            placeName: RoomEnum::HYDROPONIC_GARDEN,
            I: $I,
            daedalus: $this->daedalus,
        );
        $this->player->changePlace($garden);
    }
}
