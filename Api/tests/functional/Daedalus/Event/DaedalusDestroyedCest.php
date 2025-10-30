<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusDestroyedCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    /**
     * This test checks that we don't try to kill players multiple times when Daedalus is destroyed.
     * Not the best exit point for this, but this is how it has been detected.
     */
    public function shouldNotMultipleIncrementCyclesCountStatOnDaedalusDestroyed(FunctionalTester $I): void
    {
        $this->givenPlayerIsInDaedalus($I, CharacterEnum::ANDIE);
        $this->givenPlayerHasLivedCycles(5);
        $this->daedalus->setHull(0);
        $I->haveInRepository($this->daedalus);

        $this->eventService->callEvent(
            event: new DaedalusVariableEvent($this->daedalus, variableName: 'hull', quantity: 0, tags: [], time: new \DateTime()),
            name: VariableEventInterface::CHANGE_VARIABLE
        );

        $this->thenPlayerStatisticShouldBeIncrementedBy(5, CharacterEnum::ANDIE, $I);
    }

    private function givenPlayerIsInDaedalus(FunctionalTester $I, string $character): void
    {
        $this->player = $this->addPlayerByCharacter($I, $this->daedalus, $character);
    }

    private function givenPlayerHasLivedCycles(int $cycles): void
    {
        $this->player->getPlayerInfo()->incrementCyclesCount($cycles);
    }

    private function thenPlayerStatisticShouldBeIncrementedBy(int $increment, string $character, FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::tryFrom($character),
            userId: $this->player->getUser()->getId()
        );
        $I->assertEquals($increment, $statistic?->getCount());
    }
}
