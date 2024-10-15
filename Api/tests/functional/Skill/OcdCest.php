<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Enum\ActionTypeEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class OcdCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::OCD, $I);
    }

    public function shouldPreventPlayerToGetDirty(FunctionalTester $I): void
    {
        $this->whenIApplyDirtyStatusToPlayer($I);

        $this->thenPlayerShouldNotBeDirty($I);
    }

    public function shouldNotPreventPlayerToGetSuperDirty(FunctionalTester $I): void
    {
        $this->whenIApplySuperDirtyStatusToPlayer($I);

        $this->thenPlayerShouldBeDirty($I);
    }

    private function whenIApplyDirtyStatusToPlayer(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenIApplySuperDirtyStatusToPlayer(FunctionalTester $I): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [ActionTypeEnum::ACTION_SUPER_DIRTY->value],
            time: new \DateTime(),
        );
    }

    private function thenPlayerShouldNotBeDirty(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenPlayerShouldBeDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::DIRTY));
    }
}
