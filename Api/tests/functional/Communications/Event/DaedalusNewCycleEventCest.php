<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusNewCycleEventCest extends AbstractFunctionalTest
{
    private CreateHunterService $createHunter;
    private EventServiceInterface $eventService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createHunter = $I->grabService(CreateHunterService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setMinTransportSpawnRate(0);
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setMaxTransportSpawnRate(0);
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setHunterSpawnRate(0);
    }

    public function shouldNotTriggerContactIfDaedalusIsNotFull(FunctionalTester $I): void
    {
        $this->givenRebelBaseContactStopsAfterXCycles(10);
        $this->givenKaladaanExists($I);

        $this->whenXCyclesPass(1);

        $this->thenRebelBaseShouldNotContact(RebelBaseEnum::KALADAAN, $I);
    }

    public function shouldNotTriggerNextRebelBaseContactBeforeEightCycles(FunctionalTester $I): void
    {
        $this->givenWolfContactedToday($I);
        $this->givenKaladaanExists($I);
        $this->givenRebelBaseContactStopsAfterXCycles(10);
        $this->givenDaedalusIsFull();

        $this->whenXCyclesPass(7);
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

    public function shouldMakeAggroedTransportsLeaving(FunctionalTester $I): void
    {
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());
        $this->givenTransportIsAggroed();

        $this->whenXCyclesPass(1);

        $I->dontSeeInRepository(
            entity: Hunter::class,
            params: [
                'space' => $this->daedalus->getSpace()->getId(),
            ]
        );
    }

    public function shouldNotMakeNonAggroedTransportsLeaving(FunctionalTester $I): void
    {
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());

        $this->whenXCyclesPass(1);

        $I->seeInRepository(
            entity: Hunter::class,
            params: [
                'space' => $this->daedalus->getSpace()->getId(),
            ]
        );
    }

    public function shouldCreateNeronAnnouncementWhenAggroedTransportsLeave(FunctionalTester $I): void
    {
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());
        $this->givenTransportIsAggroed();

        $this->whenXCyclesPass(1);

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron()->getId(),
                'message' => NeronMessageEnum::MERCHANT_LEAVE,
            ]
        );
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

    private function givenTransportIsAggroed(): void
    {
        $transport = $this->daedalus->getHuntersAroundDaedalus()->getOneHunterByType(HunterEnum::TRANSPORT);
        $this->statusService->createStatusFromName(
            statusName: HunterStatusEnum::AGGROED,
            holder: $transport,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenXCyclesPass(int $x): void
    {
        $time = $this->daedalus->getCycleStartedAtOrThrow();
        $daedalusConfig = $this->daedalus->getGameConfig()->getDaedalusConfig();
        for ($i = 0; $i < $x; ++$i) {
            $time->add(new \DateInterval('PT' . $daedalusConfig->getCycleLength() . 'M'));
            $this->eventService->callEvent(
                new DaedalusCycleEvent($this->daedalus, tags: [EventEnum::NEW_CYCLE], time: $time),
                DaedalusCycleEvent::DAEDALUS_NEW_CYCLE
            );
            $this->daedalus->setCycleStartedAt($time);
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
