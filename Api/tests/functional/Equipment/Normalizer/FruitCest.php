<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\DecodeRebelSignalService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
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

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DecodeRebelSignalService $decodeRebelBase;
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);

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

    public function shouldDisplayFrugivoreBonusToBotanist(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);
        $this->givenPlayerIsFrugivore($I);

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
                    '+ 2 :pa:',
                    '+ 1 :hp:',
                    '+ 1 :pmo:',
                ],
            ],
            actual: $normalizedBanana['effects']
        );
    }

    public function shouldDisplayEffectsToPolyvalent(FunctionalTester $I): void
    {
        $this->givenPlayerIsAPolyvalent($I);

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

    public function shouldNotDisplaySiriusRebelBaseModifierOnBanana(FunctionalTester $I): void
    {
        $this->givenPlayerIsABotanist($I);
        $this->givenSiriusRebelBaseIsDecoded($I);

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

    private function givenPlayerIsAPolyvalent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I);
    }

    private function givenPlayerIsABotanist(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::BOTANIST, $I);
    }

    private function givenPlayerIsFrugivore(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::FRUGIVORE, $I);
    }

    private function givenSiriusRebelBaseIsDecoded(FunctionalTester $I): void
    {
        $siriusConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::SIRIUS]);
        $siriusRebelBase = new RebelBase(config: $siriusConfig, daedalusId: $this->daedalus->getId());
        $this->rebelBaseRepository->save($siriusRebelBase);

        $this->decodeRebelBase->execute(
            rebelBase: $siriusRebelBase,
            author: $this->player,
            progress: 100,
        );
    }
}
