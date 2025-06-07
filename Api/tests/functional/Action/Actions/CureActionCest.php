<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Cure;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CureActionCest extends AbstractFunctionalTest
{
    private ActionConfig $cureConfig;
    private Cure $cureAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RoomLogService $roomLogService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->cureConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CURE]);
        $this->cureAction = $I->grabService(Cure::class);
        $this->roomLogService = $I->grabService(serviceId: RoomLogService::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldDestroySerumAfterUsage(FunctionalTester $I): void
    {
        $this->givenChunHasSerumInInventory();
        $this->givenKuanTiIsMush($I);
        $this->whenIInoculateKuanTi();
        $this->chunShouldNotHaveSerumInInventory($I);
    }

    public function shouldRemoveMushStatusToTarget(FunctionalTester $I): void
    {
        $this->givenChunHasSerumInInventory();
        $this->givenKuanTiIsMush($I);
        $this->whenIInoculateKuanTi();
        $this->kuanTiShouldNotBeMush($I);
    }

    public function shouldRemoveAllSkillsToTarget(FunctionalTester $I): void
    {
        $this->givenChunHasSerumInInventory();
        $this->givenKuanTiHasHumanSkills($I);
        $this->givenKuanTiIsMush($I);
        $this->whenIInoculateKuanTi();
        $this->kuanTiShouldNotHaveAnySkills($I);
    }

    public function targetShouldSeePrivateLog(FunctionalTester $I): void
    {
        $this->givenChunHasSerumInInventory();
        $this->givenKuanTiIsMush($I);
        $this->whenIInoculateKuanTi();
        $this->kuanTiShouldSeeTheLog($I);
    }

    public function shouldNotChangeMushCountStatistic(FunctionalTester $I): void
    {
        $this->givenChunHasSerumInInventory();
        $this->givenKuanTiIsMush($I);
        $this->thenMushDaedalusStatisticShouldHaveCount(1, $I);
        $this->whenIInoculateKuanTi();
        $this->thenMushDaedalusStatisticShouldHaveCount(1, $I);
    }

    private function givenChunHasSerumInInventory()
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            ToolItemEnum::RETRO_FUNGAL_SERUM,
            $this->chun,
            [],
            new \DateTime(),
        );
    }

    private function givenKuanTiIsMush(FunctionalTester $I)
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function whenIInoculateKuanTi()
    {
        $serum = $this->chun->getEquipmentByNameOrThrow(ToolItemEnum::RETRO_FUNGAL_SERUM);
        $this->cureAction->loadParameters(
            $this->cureConfig,
            $serum,
            $this->chun,
            $this->kuanTi,
        );
        $this->cureAction->execute();
    }

    private function kuanTiShouldNotBeMush(FunctionalTester $I)
    {
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::MUSH));
    }

    private function chunShouldNotHaveSerumInInventory(FunctionalTester $I)
    {
        $I->assertFalse(
            $this->chun->hasEquipmentByName(ToolItemEnum::RETRO_FUNGAL_SERUM)
        );
    }

    private function givenKuanTiHasHumanSkills($I)
    {
        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->kuanTi);
    }

    private function kuanTiShouldNotHaveAnySkills(FunctionalTester $I)
    {
        $I->assertEmpty($this->kuanTi->getSkills()->toArray());
    }

    private function kuanTiShouldSeeTheLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->kuanTi)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'player_vaccinated'
            )->toArray()
        );
    }

    private function thenMushDaedalusStatisticShouldHaveCount(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getMushAmount());
    }
}
