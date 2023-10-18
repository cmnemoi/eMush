<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class DaedalusServiceCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
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

    public function testDeleteDaedalusCorrectlyDeletesHunterTargets(FunctionalTester $I)
    {
        // given there are some attacking hunters
        $this->daedalus->setHunterPoints(40);
        $hunterPoolEvent = new HunterPoolEvent(
            $this->daedalus,
            [],
            new \DateTime()
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given some hunters are targeting a player
        /** @var Hunter $hunter */
        $hunter1 = $this->daedalus->getAttackingHunters()->first();
        $hunterTarget = new HunterTarget($hunter1);
        $hunterTarget->setTargetEntity($this->player);
        $hunter1->setTarget($hunterTarget);

        $I->haveInRepository($hunterTarget);
        $I->haveInRepository($hunter1);

        // given other hunters are targeting the Daedalus
        /** @var Hunter $hunter */
        foreach ($this->daedalus->getAttackingHunters() as $hunter) {
            if ($hunter === $hunter1) {
                continue;
            }
            $hunterTarget = new HunterTarget($hunter);
            $hunterTarget->setTargetEntity($this->daedalus);
            $hunter->setTarget($hunterTarget);

            $I->haveInRepository($hunterTarget);
            $I->haveInRepository($hunter);
        }

        // when daedalus is deleted
        $this->daedalusService->closeDaedalus($this->daedalus, ['test'], new \DateTime());

        // then hunter targets are deleted
        $I->dontSeeInRepository(HunterTarget::class);
        $I->dontSeeInRepository(Hunter::class);
    }
}
