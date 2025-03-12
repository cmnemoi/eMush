<?php

declare(strict_types=1);

namespace Mush\Communications\Event;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusTravelEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private RebelBaseRepositoryInterface $rebelBaseRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
    }

    public function shouldKillContactingRebelBases(FunctionalTester $I): void
    {
        $this->givenRebelBasesAreContacting([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);

        $this->whenDaedalusTravels();

        $this->thenRebelBaseContactsShouldHaveEnded([RebelBaseEnum::WOLF, RebelBaseEnum::KALADAAN], $I);
    }

    private function givenRebelBasesAreContacting(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['name' => $rebelBaseName]);
            $this->rebelBaseRepository->save(new RebelBase($config, $this->daedalus->getId(), new \DateTimeImmutable()));
        }
    }

    private function whenDaedalusTravels(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusEvent(daedalus: $this->daedalus, tags: [], time: new \DateTime()),
            name: DaedalusEvent::TRAVEL_LAUNCHED,
        );
    }

    private function thenRebelBaseContactsShouldHaveEnded(array $rebelBaseNames, FunctionalTester $I): void
    {
        foreach ($rebelBaseNames as $rebelBaseName) {
            $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $rebelBaseName);
            $I->assertTrue($rebelBase->contactEnded(), "Rebel base {$rebelBaseName->toString()} should have ended contact");
        }
    }
}
