<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\LeaveOrbit;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractMoveDaedalusActionCest;
use Mush\Tests\FunctionalTester;

final class LeaveOrbitCest extends AbstractMoveDaedalusActionCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->moveDaedalusActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::LEAVE_ORBIT]);
        $this->moveDaedalusAction = $I->grabService(LeaveOrbit::class);

        // given daedalus is in orbit
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function testLeaveOrbitIsNotVisibleIfDaedalusIsNotInOribit(FunctionalTester $I): void
    {
        // given daedalus is not in orbit
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player leaves orbit
        $this->moveDaedalusAction->loadParameters(
            action: $this->moveDaedalusActionConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the action is not visible
        $I->assertFalse($this->moveDaedalusAction->isVisible());
    }

    public function testLeaveOrbitRemovesInOrbitStatus(FunctionalTester $I): void
    {
        // when player leaves orbit
        $this->moveDaedalusAction->loadParameters(
            action: $this->moveDaedalusActionConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then daedalus has not an in orbit status anymore
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
    }

    public function testLeaveOrbitTriggersNeronAnnouncement(FunctionalTester $I): void
    {
        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates matches the planet coordinates
        $this->daedalus->setCombustionChamberFuel($planet->getDistance());
        $this->daedalus->setOrientation($planet->getOrientation());
        $I->haveInRepository($this->daedalus);

        // when player leaves orbit
        $this->moveDaedalusAction->loadParameters(
            action: $this->moveDaedalusActionConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then neron announces orbit leave
        $I->seeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::LEAVE_ORBIT]
        );
    }

    public function testLeaveOrbitDeletesInOrbitPlanet(FunctionalTester $I): void
    {
        // given in orbit planet
        $planet = $this->planetService->createPlanet($this->player);

        // when player leaves orbit
        $this->moveDaedalusAction->loadParameters(
            action: $this->moveDaedalusActionConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then planet is deleted
        $remainingPlanets = $this->planetService->findAllByDaedalus($this->daedalus);
        $I->assertNotContains($planet, $remainingPlanets);
    }

    public function testLeaveOrbitDeletesEdgeCaseInOrbitPlanet(FunctionalTester $I): void
    {
        // given in orbit planet
        $planet = $this->planetService->createPlanet($this->player);

        // given this planet coordinates matches current daedalus coordinates
        $this->daedalus->setCombustionChamberFuel($planet->getDistance());
        $this->daedalus->setOrientation($planet->getOrientation());

        // when player leaves orbit
        $this->moveDaedalusAction->loadParameters(
            action: $this->moveDaedalusActionConfig,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then planet is deleted
        $remainingPlanets = $this->planetService->findAllByDaedalus($this->daedalus);
        $I->assertNotContains($planet, $remainingPlanets);
    }
}
