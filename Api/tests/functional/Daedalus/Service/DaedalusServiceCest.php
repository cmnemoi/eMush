<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class DaedalusServiceCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
    }

    public function testFindAllFinishedDaedaluses(FunctionalTester $I)
    {
        $finishedDaedalus = $this->createDaedalus($I);
        $this->daedalusService->endDaedalus($finishedDaedalus, 'test', new \DateTime());

        $finishedDaedaluses = $this->daedalusService->findAllFinishedDaedaluses();
        $nonFinishedDaedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();

        $I->assertCount(1, $finishedDaedaluses);
        $I->assertCount(1, $nonFinishedDaedaluses);
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

    public function testSelectAlphaMushChunNotPicked(FunctionalTester $I)
    {
        // test with 60 iterations Chun is not alpha mush because mush selection is random
        for ($i = 0; $i < 60; ++$i) {
            $this->daedalus = $this->daedalusService->selectAlphaMush($this->daedalus, new \DateTime());

            /** @var Player $chun */
            $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);
            $I->assertNull($chun->getStatusByName(PlayerStatusEnum::MUSH));
        }
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

    public function testAttributeTitles(FunctionalTester $I)
    {
        /** @var Player $chun */
        $chun = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::CHUN);
        /** @var Player $kuanTi */
        $kuanTi = $this->daedalus->getPlayers()->getPlayerByName(CharacterEnum::KUAN_TI);
        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        $this->daedalus = $this->daedalusService->attributeTitles($this->daedalus, new \DateTime());

        $I->assertEmpty($chun->getTitles());
        $I->assertEquals($kuanTi->getTitles(), [TitleEnum::NERON_MANAGER, TitleEnum::COM_MANAGER]);
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
    }
}
