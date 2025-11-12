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
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class RationCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private GameItem $banana;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private DecodeRebelSignalService $decodeRebelBase;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignalService::class);

        $this->banana = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotDisplayEffectsToRandomPlayer(FunctionalTester $I): void
    {
        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEmpty($normalizedBanana['effects']);
    }

    public function shouldDisplayEffectsToMush(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

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

    public function shouldDisplayEffectsToChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef();

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

    public function shouldNotDisplayDecompositionStatusToRandomPlayer(FunctionalTester $I): void
    {
        $this->givenBananaIsDecomposed();

        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEmpty($normalizedBanana['statuses']);
    }

    public function shouldDisplayDecompositionStatusToChef(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef();

        $this->givenBananaIsDecomposed();

        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertContains(
            needle: EquipmentStatusEnum::DECOMPOSING,
            haystack: array_map(static fn (array $status) => $status['key'], $normalizedBanana['statuses'])
        );
    }

    public function shouldDisplayDecompositionStatusToMush(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->givenBananaIsDecomposed();

        $normalizedBanana = $this->equipmentNormalizer->normalize(
            $this->banana,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertContains(
            needle: EquipmentStatusEnum::DECOMPOSING,
            haystack: array_map(static fn (array $status) => $status['key'], $normalizedBanana['statuses'])
        );
    }

    public function shouldDisplaySiriusRebelBaseModifierOnStandardRation(FunctionalTester $I): void
    {
        $this->givenPlayerIsAChef();
        $food = $this->givenPlayerHasFood(GameRationEnum::STANDARD_RATION);
        $this->givenSiriusRebelBaseIsDecoded($I);

        $normalizedFood = $this->equipmentNormalizer->normalize(
            $food,
            format: null,
            context: ['currentPlayer' => $this->player]
        );

        $I->assertEquals(
            expected: [
                'title' => 'Données sur les effets :',
                'effects' => [
                    '+ 4 :pa_cook:',
                    '+ 5 :pa:',
                    '- 1 :pmo:',
                ],
            ],
            actual: $normalizedFood['effects']
        );
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

    private function givenBananaIsDecomposed(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::DECOMPOSING,
            holder: $this->banana,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsAChef(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::CHEF, $this->player);
    }

    private function givenPlayerHasFood(string $ration): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $ration,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }
}
