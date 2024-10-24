<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
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

    public function testApronDoesNotPreventDirtyStatusToAppear(FunctionalTester $I): void
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
}
