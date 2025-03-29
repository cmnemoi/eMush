<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterNewCycleEventCest extends AbstractFunctionalTest
{
    private CreateHunterService $createHunter;
    private EventServiceInterface $eventService;
    private HunterRepositoryInterface $hunterRepository;
    private HunterServiceInterface $hunterService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->createHunter = $I->grabService(CreateHunterService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterService = $I->grabService(HunterServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->hunterRepository = $I->grabService(HunterRepositoryInterface::class);

        // avoid false positive when fire tries to reduce hull at cycle change
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setHullFireDamageRate(0);
    }

    public function testHuntersDoNotShootTheCycleAfterTheyAreSpawn(FunctionalTester $I): void
    {
        // given some hunters are spawn
        $this->daedalus->setHunterPoints(40);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        // given those hunters are aiming at the daedalus
        $this->daedalus->getHuntersAroundDaedalus()->map(static fn (Hunter $hunter) => $hunter->setTarget(new HunterTarget($hunter)));

        // given they have a 100% chance to hit
        $this->daedalus->getHuntersAroundDaedalus()
            ->map(static fn (Hunter $hunter) => $hunter->setHitChance(100))
            ->map(static fn (Hunter $hunter) => $I->haveInRepository($hunter));

        $hunter = $this->daedalus->getHuntersAroundDaedalus()->first();

        // given I launch and finish a travel
        $this->launchAndFinishTravel();

        // delete attacking hunters from travel so we only study the ones from the pool next
        $this->hunterService->delete($this->daedalus->getHuntersAroundDaedalus()->toArray());

        // given multiple cycles pass
        for ($i = 0; $i < 3; ++$i) {
            // do not spawn hunters during those cycles
            $this->daedalus->getGameConfig()->getDifficultyConfig()->setHunterSpawnRate(0);
            $daedalusEvent = new DaedalusEvent(
                daedalus: $this->daedalus,
                tags: [],
                time: new \DateTime()
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);
        }

        // given a (random) wave of hunters is spawn
        $this->daedalus->setHunterPoints(40);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $daedalusHullBeforeCycleChange = $this->daedalus->getHull();

        // when a new cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then the hunters should not shoot the daedalus, so the hull should not change
        $I->assertEquals($daedalusHullBeforeCycleChange, $this->daedalus->getHull());

        // when another cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then hunters should have shot, so daedalus should have lost hull
        $I->assertLessThan(
            expected: $daedalusHullBeforeCycleChange,
            actual: $this->daedalus->getHull()
        );
    }

    public function testUnpoolHuntersHuntersFromPoolDoNotShootRightAwayEvenIfTheyHadATargetBeforePooling(FunctionalTester $I): void
    {
        // given a hunter
        $hunter = $this->createHunterFromName($I, $this->daedalus, HunterEnum::HUNTER);

        // given the hunter is put in the pool
        $hunter->putInPool();

        // given I unpool this hunter
        $this->daedalus->setHunterPoints(10);
        $this->hunterService->unpoolHunters($this->daedalus, [], new \DateTime());

        // when a new cycle passes
        $daedalusHullBeforeCycleChange = $this->daedalus->getHull();
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then the hunter should not shoot so the hull should not change
        $I->assertEquals($daedalusHullBeforeCycleChange, $this->daedalus->getHull());
    }

    public function shouldSpawnTransport(FunctionalTester $I): void
    {
        // given I have 100% chance to spawn a transport
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setMinTransportSpawnRate(100);

        // when a new cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then one transport should be spawned
        $I->assertCount(
            1,
            $this->daedalus->getSpace()->getHuntersAroundDaedalus()->getAllHuntersByType(HunterEnum::TRANSPORT),
            'One transport should be spawned'
        );
    }

    public function shouldDeleteAggroedTransport(FunctionalTester $I): void
    {
        // given a transport is aggroed
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());
        $transport = $this->daedalus->getHuntersAroundDaedalus()->getOneHunterByType(HunterEnum::TRANSPORT);
        $this->statusService->createStatusFromName(
            statusName: HunterStatusEnum::AGGROED,
            holder: $transport,
            tags: [],
            time: new \DateTime(),
        );
        $transportId = $transport->getId();

        // when a new cycle passes
        $daedalusEvent = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DAEDALUS_NEW_CYCLE);

        // then the transport should be deleted
        $I->expectThrowable(
            new \RuntimeException("Hunter not found for id {$transportId}"),
            function () use ($transportId) {
                $this->hunterRepository->findByIdOrThrow($transportId);
            }
        );
    }

    private function launchAndFinishTravel(): void
    {
        // given a travel starts
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_LAUNCHED);

        // given a travel finishes
        $daedalusEvent = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
    }

    private function createHunterFromName(FunctionalTester $I, Daedalus $daedalus, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);
        if (!$hunterConfig) {
            throw new \Exception("Hunter config not found for hunter name {$hunterName}");
        }

        // create hunter
        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->getSpace()->addHunter($hunter);

        // given this hunter aims at the daedalus
        $hunter->setTarget(new HunterTarget($hunter));

        // given this hunter has a 100% chance to hit
        $hunter->setHitChance(100);

        $I->haveInRepository($hunter);
        $I->haveInRepository($daedalus);

        return $hunter;
    }
}
