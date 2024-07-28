<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
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
final class FruitCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private GameItem $banana;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->banana = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotDisplayEffectsToNonBotanist(FunctionalTester $I): void
    {
        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEmpty($normalizedBanana['effects']);
    }

    public function shouldDisplayEffectsToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);

        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur les effets :',
                'effects' => [
                    '+ 1 :pa_cook:',
                    '+ 1 :pa:',
                    '+ 1 :hp:',
                    '+ 1 :pmo:',
                ],
            ],
            actual: $normalizedBanana['effects']
        );
    }

    private function givenPlayerIsABotanist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::BOTANIST, $this->player));
    }
}
