<?php

namespace functional\Hunter\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Service\HunterService;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\HunterStatusEnum;

class HunterServiceCest extends AbstractFunctionalTest
{
    private HunterService $hunterService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hunterService = $I->grabService(HunterService::class);
    }

    public function testPutHuntersInPool(FunctionalTester $I)
    {
        $this->hunterService->putHuntersInPool($this->daedalus, 2);
        $I->assertCount(2, $this->daedalus->getHunterPool());
        $I->assertCount(0, $this->daedalus->getAttackingHunters());
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->putHuntersInPool($this->daedalus, 2);
        $this->hunterService->unpoolHunters($this->daedalus, 1, new \DateTime());
        $I->assertCount(1, $this->daedalus->getAttackingHunters());
        $I->assertCount(1, $this->daedalus->getHunterPool());
    }

    public function testMakeHuntersShoot(FunctionalTester $I)
    {
        $initialHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $this->hunterService->putHuntersInPool($this->daedalus, 10);
        $this->hunterService->unpoolHunters($this->daedalus, 10, new \DateTime());

        // remove the truce status
        $hunters = $I->grabEntitiesFromRepository(Hunter::class);
        /** @var Hunter $hunter */
        foreach ($hunters as $hunter) {
            $status = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
            $hunter->removeStatus($status);
        }

        $this->hunterService->makeHuntersShoot($this->daedalus->getAttackingHunters());
        $I->assertNotEquals($initialHull, $this->daedalus->getHull());
    }
}
