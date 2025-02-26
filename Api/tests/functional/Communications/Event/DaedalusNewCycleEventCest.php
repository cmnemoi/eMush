<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusNewCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNotTriggerContactIfDaedalusIsNotFull(FunctionalTester $I): void
    {
        $this->givenRebelBaseContactStopsAfterXCycles(10);
        $this->givenKaladaanExists($I);

        $this->whenXCyclesPass(1);

        $this->thenRebelBaseShouldNotContact(RebelBaseEnum::KALADAAN, $I);
    }

    public function shouldTriggerNextRebelBaseContactAfterEightCycles(FunctionalTester $I): void
    {
        $this->givenWolfContactedToday($I);
        $this->givenKaladaanExists($I);
        $this->givenRebelBaseContactStopsAfterXCycles(10);
        $this->givenDaedalusIsFull();

        $this->whenXCyclesPass(8);

        $this->thenRebelBaseShouldContact(RebelBaseEnum::KALADAAN, $I);
    }

    public function shouldKillRebelBaseContactAfterSetUpDuration(FunctionalTester $I): void
    {
        $this->givenWolfContactedToday($I);
        $this->givenRebelBaseContactStopsAfterXCycles(2);
        $this->givenDaedalusIsFull();

        $this->whenXCyclesPass(2);

        $this->thenRebelBaseShouldNotContact(RebelBaseEnum::WOLF, $I);
    }

    private function givenWolfContactedToday(FunctionalTester $I): void
    {
        $wolfConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::WOLF]);
        $wolf = new RebelBase($wolfConfig, $this->daedalus->getId());
        $this->rebelBaseRepository->save($wolf);
        $wolf->triggerContact();
    }

    private function givenKaladaanExists(FunctionalTester $I): void
    {
        $kaladaanConfig = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => RebelBaseEnum::KALADAAN]);
        $this->rebelBaseRepository->save(
            new RebelBase($kaladaanConfig, $this->daedalus->getId())
        );
    }

    private function givenRebelBaseContactStopsAfterXCycles(int $numberOfCycles): void
    {
        $rebelBaseContactDurationStatus = $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime()
        );
        $this->statusService->updateCharge(
            chargeStatus: $rebelBaseContactDurationStatus,
            delta: $numberOfCycles,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE
        );
    }

    private function givenDaedalusIsFull(): void
    {
        $this->daedalus->getDaedalusInfo()->startDaedalus();
    }

    private function whenXCyclesPass(int $x): void
    {
        $time = new \DateTime();
        for ($i = 0; $i < $x; ++$i) {
            $time->modify(\sprintf('+%d minutes', $this->daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCycleLength()));
            $this->eventService->callEvent(
                new DaedalusCycleEvent($this->daedalus, tags: [EventEnum::NEW_CYCLE], time: $time),
                DaedalusCycleEvent::DAEDALUS_NEW_CYCLE
            );
        }
    }

    private function thenRebelBaseShouldContact(RebelBaseEnum $rebelBaseName, FunctionalTester $I): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow(
            $this->daedalus->getId(),
            $rebelBaseName
        );

        $I->assertFalse($rebelBase->isNotContacting(), "Rebel base {$rebelBaseName->toString()} should be contacting");
    }

    private function thenRebelBaseShouldNotContact(RebelBaseEnum $rebelBaseName, FunctionalTester $I): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow(
            $this->daedalus->getId(),
            $rebelBaseName
        );

        $I->assertTrue($rebelBase->isNotContacting(), "Rebel base {$rebelBaseName->toString()} should not be contacting");
    }
}
