<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FinishDaedalusEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatisticRepositoryInterface $statisticRepository;
    private Player $andie;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldIncrementAndieStatisticWhenDaedalusEnds(FunctionalTester $I): void
    {
        $this->givenAndieIsInDaedalus($I);
        $this->givenAndieHasLivedCycles(5);

        $this->whenDaedalusEndsWithSuperNova();

        $this->thenAndieStatisticShouldBeIncrementedBy(5, $I);
    }

    private function givenAndieIsInDaedalus(FunctionalTester $I): void
    {
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
    }

    private function givenAndieHasLivedCycles(int $cycles): void
    {
        $this->andie->getPlayerInfo()->incrementCyclesCount($cycles);
    }

    private function whenDaedalusEndsWithSuperNova(): void
    {
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            [EndCauseEnum::SUPER_NOVA],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
    }

    private function thenAndieStatisticShouldBeIncrementedBy(int $increment, FunctionalTester $I): void
    {
        $andieStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::ANDIE, $this->andie->getUser()->getId());
        $I->assertEquals($increment, $andieStatistic?->getCount());
    }
}
