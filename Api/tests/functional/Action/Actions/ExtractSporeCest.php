<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExtractSporeCest extends AbstractFunctionalTest
{
    private ActionConfig $extractSporeActionConfig;
    private ExtractSpore $extractSporeAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->extractSporeAction = $I->grabService(ExtractSpore::class);
        $this->extractSporeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'extract_spore']);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given Kuan Ti is Mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );
    }

    public function apronDoesNotPreventDirtyStatusToAppear(FunctionalTester $I): void
    {
        // given Kuan Ti has an apron
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime()
        );

        // when Kuan Ti extracts spore
        $this->extractSporeAction->loadParameters(
            actionConfig: $this->extractSporeActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
        $this->extractSporeAction->execute();

        // then Kuan Ti should have the dirty status
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::DIRTY));
    }

    public function fertileShouldMakeTheActionFree(FunctionalTester $I): void
    {
        // given Kuan Ti has Fertile skill
        $this->addSkillToPlayer(SkillEnum::FERTILE, $I, $this->kuanTi);

        // when Kuan Ti tries to extract spore
        $this->extractSporeAction->loadParameters(
            actionConfig: $this->extractSporeActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );

        // then action should cost 0 action points
        $I->assertEquals(0, $this->extractSporeAction->getActionPointCost());
    }

    public function sporesCreatedCounterShouldIncrementWhenASporeIsExctracted(FunctionalTester $I): void
    {
        // given the spore created counter is set to 0
        $this->daedalus->getDaedalusInfo()->setDaedalusStatistics(new DaedalusStatistics(sporesCreated: 0));

        // when Kuan Ti extracts spore
        $this->extractSporeAction->loadParameters(
            actionConfig: $this->extractSporeActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
        $this->extractSporeAction->execute();

        // then the spores created counter should be incremented to 1.
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getSporesCreated(), 'sporesCreated should be 1.');
    }

    public function antisporeGasShouldLimitSporeExtraction(FunctionalTester $I): void
    {
        $this->givenAntisporeGasIsCompleted($I);

        $this->givenKuanTiExtractedTwoSpores();

        $this->whenKuanTiTriesToExtractSpore();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT);
    }

    public function constipasporeSerumShouldIncreaseActionCost(FunctionalTester $I): void
    {
        $this->givenActionCostActionPoints(2, $I);

        $this->givenConstipasporeSerumIsCompleted($I);

        $this->whenKuanTiTriesToExtractSpore();

        $this->thenActionShouldCostActionPoints(4, $I);
    }

    public function fertileShouldMakeTheActionFreeEvenWithConstipasporeSerum(FunctionalTester $I): void
    {
        $this->givenActionCostActionPoints(2, $I);

        $this->givenConstipasporeSerumIsCompleted($I);

        // given Kuan Ti has Fertile skill
        $this->addSkillToPlayer(SkillEnum::FERTILE, $I, $this->kuanTi);

        $this->whenKuanTiTriesToExtractSpore();

        // then action should cost 0 action points
        $I->assertEquals(0, $this->extractSporeAction->getActionPointCost());
    }

    public function shouldNotGiveNotificationWhenGettingDirty(FunctionalTester $I): void
    {
        $this->givenKuanTiHasNoNotifications();

        $this->givenKuanTiIsNotDirty($I);

        $this->whenKuanTiExtractsSpore();

        $this->thenKuanTiShouldBeDirty($I);

        $this->thenKuanTiHasNoNotifications($I);
    }

    private function givenAntisporeGasIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::ANTISPORE_GAS),
            author: $this->chun,
            I: $I
        );
    }

    private function givenKuanTiExtractedTwoSpores(): void
    {
        $this->extractSporeAction->loadParameters(
            actionConfig: $this->extractSporeActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
        $this->extractSporeAction->execute();
        $this->extractSporeAction->execute();
    }

    private function givenActionCostActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $this->extractSporeActionConfig->setActionCost($actionPoints);
    }

    private function givenConstipasporeSerumIsCompleted(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::CONSTIPASPORE_SERUM),
            author: $this->chun,
            I: $I
        );
    }

    private function givenKuanTiHasNoNotifications(): void
    {
        $this->kuanTi->deleteNotification();
    }

    private function givenKuanTiIsNotDirty(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function whenKuanTiTriesToExtractSpore(): void
    {
        $this->extractSporeAction->loadParameters(
            actionConfig: $this->extractSporeActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi
        );
    }

    private function whenKuanTiExtractsSpore(): void
    {
        $this->whenKuanTiTriesToExtractSpore();
        $this->extractSporeAction->execute();
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($message, $this->extractSporeAction->cannotExecuteReason());
    }

    private function thenActionShouldCostActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->extractSporeAction->getActionPointCost());
    }

    private function thenKuanTiShouldBeDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenKuanTiHasNoNotifications(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasNotification());
    }
}
