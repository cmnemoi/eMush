<?php

namespace functional\Daedalus\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Daedalus\Service\DaedalusService;

class DaedalusServiceCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function testFindAllDaedalusesOnCycleChange(FunctionalTester $I)
    {
        $lockedUpDaedalus = $this->createDaedalus($I);
        $lockedUpDaedalus->setIsCycleChange(true);
        $this->daedalusService->persist($lockedUpDaedalus);
        $I->refreshEntities($lockedUpDaedalus);

        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        $lockedUpDaedaluses = $this->daedalusService->findAllDaedalusesOnCycleChange();

        $I->assertCount(2, $nonFinishedDaedaluses);
        $I->assertCount(1, $lockedUpDaedaluses);
    }

    public function testSkipCycleChange(FunctionalTester $I)
    {
        $lockedUpDaedalus = $this->createDaedalus($I);
        $lockedUpDaedalus->setIsCycleChange(true);
        $this->daedalusService->persist($lockedUpDaedalus);
        $I->refreshEntities($lockedUpDaedalus);

        $this->daedalusService->skipCycleChange($lockedUpDaedalus);

        $lockedUpDaedaluses = $this->daedalusService->findAllDaedalusesOnCycleChange();

        $I->assertEmpty($lockedUpDaedaluses);
    }
}
