<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\TrapCloset;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TrapClosetCest extends AbstractFunctionalTest
{
    private ActionConfig $trapClosetConfig;
    private TrapCloset $trapClosetAction;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->trapClosetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TRAP_CLOSET]);
        $this->trapClosetAction = $I->grabService(TrapCloset::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given KT is Mush so he has the action available
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveASporeAvailable(FunctionalTester $I): void
    {
        // given KT has no spore
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );

        // when KT tries to trap the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );

        // then the action should not be visible
        $I->assertFalse($this->trapClosetAction->isVisible());
    }

    public function shouldConsumeOnePlayerSpore(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when KT traps the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();

        // then KT should have no spores left
        $I->assertEquals(
            expected: 0,
            actual: $this->kuanTi->getSpores(),
        );
    }

    public function shouldAddATrappedStatusToPlayerRoom(FunctionalTester $I): void
    {
        // given KT has one spore
        $this->kuanTi->setSpores(1);

        // when KT traps the closet
        $this->trapClosetAction->loadParameters(
            actionConfig: $this->trapClosetConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
        $this->trapClosetAction->execute();

        // then the room should have a trapped status
        $I->assertTrue($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }
}