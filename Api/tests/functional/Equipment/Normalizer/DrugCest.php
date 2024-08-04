<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameDrugEnum;
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
            equipmentName: GameDrugEnum::TWINOID,
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

    private function givenPlayerIsANurse(FunctionalTester $I): void
    {
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::NURSE]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::NURSE, $this->chun));
    }
}
