<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AdvanceDaedalus;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Tests\AbstractMoveDaedalusActionCest;
use Mush\Tests\FunctionalTester;

final class AdvanceDaedalusCest extends AbstractMoveDaedalusActionCest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->moveDaedalusActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ADVANCE_DAEDALUS]);
        $this->moveDaedalusAction = $I->grabService(AdvanceDaedalus::class);
    }

    public function testAdvanceDaedalusIsNotVisibleIfDaedalusIsInOrbit(FunctionalTester $I): void
    {
        // given daedalus is in orbit
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then the action is not visible
        $I->assertFalse($this->moveDaedalusAction->isVisible());
    }

    public function testAdvanceDaedalusCreatesInOrbitStatusIfGoingToAPlanet(FunctionalTester $I): void
    {
        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates matches the planet coordinates
        $this->daedalus->setCombustionChamberFuel($planet->getDistance());
        $this->daedalus->setOrientation($planet->getOrientation());
        $I->haveInRepository($this->daedalus);

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then daedalus has an in orbit status
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
    }

    public function testAdvanceDaedalusDoesNotCreatesInOrbitStatusIfNotGoingToAPlanet(FunctionalTester $I): void
    {
        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates does not match the planet coordinates

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then daedalus does not have an in orbit status
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::IN_ORBIT));
    }

    public function testAdvanceDaedalusTriggersNeronAnnouncementIfGoingToAPlanet(FunctionalTester $I): void
    {
        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates matches the planet coordinates
        $this->daedalus->setCombustionChamberFuel($planet->getDistance());
        $this->daedalus->setOrientation($planet->getOrientation());
        $I->haveInRepository($this->daedalus);

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then neron announces the travel
        $I->seeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::TRAVEL_PLANET]
        );
    }

    public function testAdvanceDaedalusTriggersNeronAnnouncementIfNotGoingToAPlanet(FunctionalTester $I): void
    {
        // given player found a planet
        $planet = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);

        // given Daedalus coordinates does not match the planet coordinates

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then neron announces the travel
        $I->seeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::TRAVEL_DEFAULT]
        );
        $I->dontSeeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::TRAVEL_PLANET]
        );
    }

    public function testAdvanceDaedalusDeletesAllPlanetsIfGoingToAPlanet(FunctionalTester $I): void
    {
        // given player found two planets
        $planet = $this->planetService->createPlanet($this->player);
        $planet2 = $this->planetService->createPlanet($this->player);
        $I->haveInRepository($planet);
        $I->haveInRepository($planet2);

        // given Daedalus coordinates does not match the planet coordinates

        // when player advances daedalus
        $this->moveDaedalusAction->loadParameters(
            actionConfig: $this->moveDaedalusActionConfig,
            actionProvider: $this->commandTerminal,
            player: $this->player,
            target: $this->commandTerminal
        );
        $this->moveDaedalusAction->execute();

        // then all planets are deleted
        $remainingPlanets = $this->planetService->findAllByDaedalus($this->daedalus);
        $I->assertEmpty($remainingPlanets);
    }
}
