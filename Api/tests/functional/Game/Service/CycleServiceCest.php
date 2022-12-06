<?php

namespace Mush\Tests\functional\Game\Service;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
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

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['timezone' => 'UTC']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 1,
            'day' => 1,
            'oxygen' => 32,
            'createdAt' => $daedalusCreatedAt,
            'cycleStartedAt' => $daedalusCreatedAt,
        ]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);

        $this->cycleService->handleCycleChange($daedalusNewCycleAt, $daedalus);

        $I->assertEquals(1, $daedalus->getCycle());
        $I->assertEquals(2, $daedalus->getDay());
    }

    public function testDateLastCycleIsUpdated(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['timezone' => 'UTC']);

        $daedalusCreatedAt = new \DateTime('01-01-2000');
        $daedalusNewCycleAt = new \DateTime('02-01-2000');

        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 1,
            'day' => 1,
            'oxygen' => 32,
            'createdAt' => $daedalusCreatedAt,
            'cycleStartedAt' => $daedalusCreatedAt,
        ]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);

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
