<?php

namespace functional\Hunter\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Hunter\Service\HunterService;

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
        $I->assertCount(2, $this->daedalus->getSpace()->getAllHunters());
        $I->assertCount(2, $this->daedalus->getHunterPool());
        $I->assertCount(0, $this->daedalus->getAttackingHunters());
    }

    public function testUnpoolHunters(FunctionalTester $I)
    {
        $this->hunterService->putHuntersInPool($this->daedalus, 2);
        $this->hunterService->unpoolHunters($this->daedalus, 1);
        $I->assertCount(1, $this->daedalus->getAttackingHunters());
        $I->assertCount(1, $this->daedalus->getHunterPool());
    }
}
