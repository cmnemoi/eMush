<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HyperfreezeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Hyperfreeze $hyperfreezeAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HYPERFREEZE]);
        $this->hyperfreezeAction = $I->grabService(Hyperfreeze::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    #[DataProvider('decompositionStatusProvider')]
    public function shouldCreateADecomposingStandardRationFromADecomposingAlienSteak(
        FunctionalTester $I,
        Example $decomposingStatus,
    ): void {
        // given I have a decomposing alien steak in Chun's place
        $alienSteak = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::ALIEN_STEAK,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createStatusFromName(
            statusName: $decomposingStatus['status'],
            holder: $alienSteak,
            tags: [],
            time: new \DateTime()
        );

        // given a superfreezer in Chun's place
        $superfreezer = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::SUPERFREEZER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when I hyperfreeze the alien steak
        $this->hyperfreezeAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $superfreezer,
            player: $this->chun,
            target: $alienSteak
        );
        $this->hyperfreezeAction->execute();

        // then I should have a decomposing standard ration in Chun's inventory
        $ration = $this->chun->getEquipmentByName(GameRationEnum::STANDARD_RATION);
        $I->assertNotNull($ration);
        $I->assertTrue($ration->hasStatus($decomposingStatus['status']));
    }

    protected function decompositionStatusProvider(): array
    {
        return [
            ['status' => EquipmentStatusEnum::UNSTABLE],
            ['status' => EquipmentStatusEnum::HAZARDOUS],
            ['status' => EquipmentStatusEnum::DECOMPOSING],
        ];
    }
}
