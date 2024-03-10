<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\TurnDaedalusRight;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractTurnDaedalusActionCest;
use Mush\Tests\FunctionalTester;

final class TurnDaedalusRightCest extends AbstractTurnDaedalusActionCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->turnDaedalusAction = $I->grabService(TurnDaedalusRight::class);
    }

    public function testTurnDaedalusActionNotExecutableIfLateralReactorIsBroken(FunctionalTester $I): void
    {
        // given daedalus bravo reactor is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->bravoLateralReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus right
        $this->turnDaedalusAction->loadParameters(
            action: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::BRAVO_REACTOR_BROKEN, $this->turnDaedalusAction->cannotExecuteReason());
    }

    public function testTurnDaedalusActionSuccessChangesCorrectlyDaedalusOrientation(FunctionalTester $I): void
    {
        // given daedalus is facing north
        $I->assertEquals(expected: SpaceOrientationEnum::NORTH, actual: $this->daedalus->getOrientation());

        // when player turns daedalus right
        $this->turnDaedalusAction->loadParameters(
            action: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then daedalus is facing east
        $I->assertEquals(expected: SpaceOrientationEnum::EAST, actual: $this->daedalus->getOrientation());

        // when player turns daedalus right again
        $this->turnDaedalusAction->loadParameters(
            action: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then daedalus is facing south
        $I->assertEquals(expected: SpaceOrientationEnum::SOUTH, actual: $this->daedalus->getOrientation());
    }
}
