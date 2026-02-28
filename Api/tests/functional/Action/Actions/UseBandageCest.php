<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\UseBandage;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class UseBandageCest extends AbstractExplorationTester
{
    private ActionConfig $actionConfig;
    private UseBandage $useBandage;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $bandage;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::USE_BANDAGE]);
        $this->useBandage = $I->grabService(UseBandage::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenChunHasBandage();
    }

    public function shouldHealDuringExpedition(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);
        $this->givenChunHasHealthPoints(10);

        $this->whenChunUsesBandage();

        $this->thenChunShouldHaveHealthPoints(12, $I);
    }

    public function shouldHealDuringExpeditionWhenLost(FunctionalTester $I): void
    {
        $this->givenPlayersAreInAnExpedition($I);
        $this->givenChunIsLost($I);
        $this->givenChunHasHealthPoints(10);

        $this->whenChunUsesBandage();

        $this->thenChunShouldHaveHealthPoints(12, $I);
    }

    private function givenChunHasBandage(): void
    {
        $this->bandage = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::BANDAGE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayersAreInAnExpedition(FunctionalTester $I): void
    {
        $this->createExploration(
            planet: $this->createPlanet(
                sectors: [PlanetSectorEnum::OXYGEN],
                functionalTester: $I
            ),
            explorators: $this->players,
        );
    }

    private function givenChunHasHealthPoints(int $quantity): void
    {
        $this->chun->setHealthPoint(10);
    }

    private function givenChunIsLost(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenChunUsesBandage(): void
    {
        $this->useBandage->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->bandage,
            player: $this->chun,
            target: $this->bandage,
        );
        $this->useBandage->execute();
    }

    private function thenChunShouldHaveHealthPoints(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->chun->getHealthPoint());
    }
}
