<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
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

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

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
}
