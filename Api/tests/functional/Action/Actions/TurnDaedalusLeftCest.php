<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\TurnDaedalusLeft;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractTurnDaedalusActionCest;
use Mush\Tests\FunctionalTester;

final class TurnDaedalusLeftCest extends AbstractTurnDaedalusActionCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->turnDaedalusAction = $I->grabService(TurnDaedalusLeft::class);
    }

    public function testTurnDaedalusActionNotExecutableIfLateralReactorIsBroken(FunctionalTester $I): void
    {
        // given daedalus is traveling
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->bravoLateralReactor,
            tags: [],
            time: new \DateTime(),
        );

        // when player turns daedalus left
        $this->turnDaedalusAction->loadParameters(
            action: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then the action is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::LATERAL_REACTOR_BROKEN, $this->turnDaedalusAction->cannotExecuteReason());
    }

    public function testTurnDaedalusActionSuccessChangesCorrectlyDaedalusOrientation(FunctionalTester $I): void
    {
        // given daedalus is facing north
        $I->assertEquals(expected: SpaceOrientationEnum::NORTH, actual: $this->daedalus->getOrientation());

        // when player turns daedalus left
        $this->turnDaedalusAction->loadParameters(
            action: $this->turnDaedalusConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->turnDaedalusAction->execute();

        // then daedalus is facing west
        $I->assertEquals(expected: SpaceOrientationEnum::WEST, actual: $this->daedalus->getOrientation());

        // when player turns daedalus left again
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
