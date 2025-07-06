<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\TryKube;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MovementPointConversionCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    private TryKube $tryKubeAction;
    private ActionConfig $tryKubeConfig;

    private GameEquipment $kube;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->tryKubeAction = $I->grabService(TryKube::class);
        $this->tryKubeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TRY_KUBE]);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->kube = $this->givenMadKubeInTheRoom();
    }

    public function testBasicConversion(FunctionalTester $I)
    {
        $this->givenChunHasAP(1);

        $this->givenChunHasMP(0);

        $this->givenTryKubeCostMPInstead(1);

        $this->whenChunTryTheKUBE();

        $this->thenChunShouldHaveAP(0, $I);

        $this->thenChunShouldHaveMP(2, $I);
    }

    public function testConversionWithIncreasedMovementCost(FunctionalTester $I)
    {
        $this->givenChunHasAP(1);

        $this->givenChunHasMP(0);

        $this->givenTryKubeCostMPInstead(2);

        $this->whenChunTryTheKUBE();

        $this->thenChunShouldHaveAP(0, $I);

        $this->thenChunShouldHaveMP(1, $I);
    }

    public function testSeveralConversionRequired(FunctionalTester $I)
    {
        $this->givenChunHasAP(2);

        $this->givenChunHasMP(0);

        $this->givenTryKubeCostMPInstead(5);

        $this->whenChunTryTheKUBE();

        $this->thenChunShouldHaveAP(0, $I);

        $this->thenChunShouldHaveMP(1, $I);
    }

    public function testConversionImpossible(FunctionalTester $I)
    {
        $this->givenChunHasAP(0);

        $this->givenChunHasMP(0);

        $this->givenTryKubeCostMPInstead(1);

        $this->whenChunTryTheKUBE();

        $this->thenChunCantTryTheKUBE($I);
    }

    private function givenMadKubeInTheRoom(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MAD_KUBE,
            equipmentHolder: $this->player->getPlace(),
            reasons: ['test'],
            time: new \DateTime()
        );
    }

    private function givenTryKubeCostMPInstead(int $mp): void
    {
        $this->tryKubeConfig->setActionCost(0);
        $this->tryKubeConfig->setMovementCost($mp);
    }

    private function givenChunHasAP(int $ap): void
    {
        $this->player->setActionPoint($ap);
    }

    private function givenChunHasMP(int $mp): void
    {
        $this->player->setMovementPoint($mp);
    }

    private function whenChunTryTheKUBE(): void
    {
        $this->tryKubeAction->loadParameters(
            $this->tryKubeConfig,
            $this->kube,
            $this->player,
            $this->kube,
        );

        $this->tryKubeAction->execute();
    }

    private function thenChunShouldHaveAP(int $ap, FunctionalTester $I): void
    {
        $I->assertEquals($ap, $this->player->getActionPoint());
    }

    private function thenChunShouldHaveMP(int $mp, FunctionalTester $I): void
    {
        $I->assertEquals($mp, $this->player->getMovementPoint());
    }

    private function thenChunCantTryTheKUBE(FunctionalTester $I): void
    {
        $I->assertTrue($this->tryKubeAction->isVisible());
        $I->assertNotNull($this->tryKubeAction->cannotExecuteReason());
    }
}
