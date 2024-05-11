<?php

namespace Mush\Tests\functional\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleChangeCest extends AbstractFunctionalTest
{
    private CycleServiceInterface $cycleService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->cycleService = $I->grabService(CycleServiceInterface::class);

        // given Chun has 20000 health point, 20000 morale points
        $this->chun->getPlayerInfo()->getCharacterConfig()->setMaxHealthPoint(20000);
        $this->chun->getPlayerInfo()->getCharacterConfig()->setMaxMoralPoint(20000);

        $this->chun->setPlayerVariables($this->chun->getPlayerInfo()->getCharacterConfig());

        $this->chun->setHealthPoint(20000);
        $this->chun->setMoralPoint(20000);

        // given Daedalus has 20000 oxygen and 20000 hull points
        $this->daedalus->getDaedalusConfig()->setMaxOxygen(20000);
        $this->daedalus->getDaedalusConfig()->setMaxHull(20000);

        $this->daedalus->setDaedalusVariables($this->daedalus->getDaedalusConfig());
        $this->daedalus->setOxygen(20000);
        $this->daedalus->setHull(20000);

        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    public function testChangeManyCyclesSubscriber(FunctionalTester $I)
    {
        $now = new \DateTime();
        $lastCycle = (clone $now)->sub(new \DateInterval('PT151H')); // subtract 150 h (ie 50 cycles)
        $this->daedalus->setCycleStartedAt($now);

        $this->cycleService->handleDaedalusAndExplorationCycleChanges($lastCycle, $this->daedalus);

        $I->assertEquals($this->daedalus->getDaedalusInfo()->getGameStatus(), GameStatusEnum::CURRENT);
        $I->assertTrue($this->chun->isAlive());
        $I->assertFalse($this->daedalus->isCycleChange());
        $I->assertEquals($this->daedalus->getDay(), 7);
    }

    public function testMultipleCycleChangeCallsTriggerItOnlyOnce(FunctionalTester $I): void
    {
        $lastCycle = (new \DateTime())->sub(new \DateInterval('PT3H1M')); // subtract 3 h and 1 minute (ie 1 cycle)
        $now = new \DateTime();

        $this->daedalus->setCycle(1);
        $this->daedalus->setCycleStartedAt($lastCycle);
        $I->haveInRepository($this->daedalus);

        $I->assertFalse($this->daedalus->isCycleChange());
        for ($i = 0; $i < 10; ++$i) {
            $this->cycleService->handleDaedalusAndExplorationCycleChanges($now, $this->daedalus);
        }

        $I->assertFalse($this->daedalus->isCycleChange());
        $I->assertEquals($this->daedalus->getCycle(), 2);
    }
}
