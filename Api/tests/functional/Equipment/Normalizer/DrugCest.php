<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class DrugCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private GameItem $drug;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->drug = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'plus_one_ap_drug',
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotDisplayEffectsToNonNurse(FunctionalTester $I): void
    {
        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertEmpty($normalizedDrug['effects']);
    }

    public function shouldDisplayEffectsToNurse(FunctionalTester $I): void
    {
        $this->givenPlayerIsANurse($I);

        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertNotEmpty($normalizedDrug['effects']);
    }

    public function shouldDisplayEffectsToPolyvalent(FunctionalTester $I): void
    {
        $this->givenPlayerIsAPolyvalent($I);

        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertNotEmpty($normalizedDrug['effects']);
    }

    public function shouldDisplayEffectsToBiologist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABiologist($I);

        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertNotEmpty($normalizedDrug['effects']);
    }

    public function shouldDisplayEffectsToMedic(FunctionalTester $I): void
    {
        $this->givenPlayerIsAMedic($I);

        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertNotEmpty($normalizedDrug['effects']);
    }

    public function shouldNotDisplayEffectsToChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef($I);

        $normalizedDrug = $this->equipmentNormalizer->normalize(
            $this->drug,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        $I->assertEmpty($normalizedDrug['effects']);
    }

    private function givenPlayerIsAPolyvalent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I);
    }

    private function givenPlayerIsANurse(FunctionalTester $I): void
    {
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::NURSE]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::NURSE, $this->chun));
    }

    private function givenPlayerIsABiologist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::BIOLOGIST, $I);
    }

    private function givenPlayerIsAMedic(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MEDIC, $I);
    }

    private function givenPlayerIsAChef(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CHEF, $I);
    }
}
