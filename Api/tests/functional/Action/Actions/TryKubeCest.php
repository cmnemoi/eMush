<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\TryKube;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TryKubeCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    private ActionConfig $tryKubeConfig;
    private TryKube $tryKube;
    private GameItem $madKube;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->tryKubeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TRY_KUBE]);
        $this->tryKube = $I->grabService(TryKube::class);
        $this->madKube = $this->givenMadKubeInTheRoom();
    }

    public function shouldBeExecutable(FunctionalTester $I)
    {
        $this->whenPlayerTriesToTryTheKube();

        $I->assertTrue($this->tryKube->isVisible());
        $I->assertNull($this->tryKube->cannotExecuteReason());
    }

    public function testTryTheKube(FunctionalTester $I)
    {
        $initialActionPoints = $this->player->getActionPoint();
        $this->whenPlayerTriesTheKube();

        $I->assertEquals($initialActionPoints - 1, $this->player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TRY_KUBE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function shouldSolveTheKube(FunctionalTester $I)
    {
        $this->whenPlayerSolvesTheKube();

        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::POINTLESS_PLAYER));
        $I->assertEquals(5, $this->player->getTriumph());
    }

    public function shouldNotGiveExtraGloryWhenSolvingTheKubeAgain(FunctionalTester $I)
    {
        $this->whenPlayerSolvesTheKube();
        $this->whenPlayerSolvesTheKube();

        $I->assertEquals(5, $this->player->getTriumph());
    }

    private function givenMadKubeInTheRoom(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MAD_KUBE,
            equipmentHolder: $this->player->getPlace(),
            reasons: ['test'],
            time: new \DateTime()
        );
    }

    private function whenPlayerTriesToTryTheKube(): void
    {
        $this->tryKube->loadParameters(
            actionConfig: $this->tryKubeConfig,
            actionProvider: $this->madKube,
            player: $this->player,
            target: $this->madKube
        );
    }

    private function whenPlayerTriesTheKube(): void
    {
        $this->whenPlayerTriesToTryTheKube();
        $this->tryKube->execute();
    }

    private function whenPlayerSolvesTheKube(): void
    {
        $this->tryKubeConfig->setOutputQuantity(100); // 100% to succeed when trying the kube
        $this->whenPlayerTriesTheKube();
    }
}
