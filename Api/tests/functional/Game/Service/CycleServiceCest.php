<?php

namespace Mush\Tests\functional\Game\Service;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleService;

class CycleServiceCest
{
    private CycleService $cycleService;

    public function _before(FunctionalTester $I)
    {
        $this->cycleService = $I->grabService(CycleService::class);
    }

    public function testMultipleCycleElapsed(FunctionalTester $I)
    {
        $daedalusCreatedAt = new \DateTime('01-01-2000');
        $daedalusNewCycleAt = new \DateTime('02-01-2000');

        $gameConfig = $I->have(GameConfig::class, [
            'timezone' => 'UTC',
            'cycleLength' => 60 * 3,
        ]);

        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 1,
            'day' => 1,
            'oxygen' => 32,
            'gameConfig' => $gameConfig,
            'createdAt' => $daedalusCreatedAt,
            'cycleStartedAt' => $daedalusCreatedAt,
            'gameStatus' => GameStatusEnum::STARTING,
        ]);

        $this->cycleService->handleCycleChange($daedalusNewCycleAt, $daedalus);

        $I->assertEquals(1, $daedalus->getCycle());
        $I->assertEquals(2, $daedalus->getDay());
    }

    public function testDateLastCycleIsUpdated(FunctionalTester $I)
    {
        $daedalusCreatedAt = new \DateTime('01-01-2000');
        $daedalusNewCycleAt = new \DateTime('02-01-2000');

        $gameConfig = $I->have(GameConfig::class, [
            'timezone' => 'UTC',
            'cycleLength' => 60 * 3,
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 1,
            'day' => 1,
            'oxygen' => 32,
            'gameConfig' => $gameConfig,
            'createdAt' => $daedalusCreatedAt,
            'cycleStartedAt' => $daedalusCreatedAt,
            'gameStatus' => GameStatusEnum::STARTING,
        ]);

        $this->cycleService->handleCycleChange($daedalusNewCycleAt, $daedalus);

        $I->assertEquals(1, $daedalus->getCycle());
        $I->assertEquals(2, $daedalus->getDay());
        $I->assertEquals($daedalusNewCycleAt->format(\DateTime::ATOM), $daedalus->getCycleStartedAt()->format(\DateTime::ATOM));

        $I->seeInRepository(Daedalus::class, [
            'id' => $daedalus->getId(),
            'cycleStartedAt' => $daedalus->getCycleStartedAt()->format(\DateTime::ATOM),
        ]);
    }
}
