<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusNewCycleEventCest extends AbstractFunctionalTest
{
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;
    private EventServiceInterface $eventService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);

        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
    }

    public function shouldTriggerNextRebelBaseContactAfter8Cycles(FunctionalTester $I): void
    {
        $this->givenWolfContactedToday($I);
        $this->givenKaladaanExists($I);

        $this->whenXCyclesPass(8);

        $this->thenRebelBaseShouldContact(RebelBaseEnum::KALADAAN, $I);
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
}
