<?php

namespace functional\Hunter\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Service\HunterService;
use Mush\Status\Enum\HunterStatusEnum;

class HunterServiceCest extends AbstractFunctionalTest
{
    private HunterService $hunterService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hunterService = $I->grabService(HunterService::class);
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
        $I->assertCount(0, $this->daedalus->getHunterPool());
    }

    public function testMakeHuntersShoot(FunctionalTester $I)
    {
        $initialHull = $this->daedalus->getGameConfig()->getDaedalusConfig()->getInitHull();
        $this->daedalus->setHunterPoints(100);
        $this->hunterService->unpoolHunters($this->daedalus, new \DateTime());

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
