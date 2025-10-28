<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
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

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    #[DataProvider('characterDataProvider')]
    public function shouldIncrementCharacterStatisticWhenDaedalusEnds(FunctionalTester $I, Example $example): void
    {
        $this->givenPlayerIsInDaedalus($I, $example['character']);
        $this->givenPlayerHasLivedCycles(5);

        $this->whenDaedalusEndsWithSuperNova();

        $this->thenPlayerStatisticShouldBeIncrementedBy(5, $example['character'], $I);
    }

    protected function characterDataProvider(): array
    {
        return [
            ['character' => CharacterEnum::ANDIE],
            ['character' => CharacterEnum::CHAO],
            ['character' => CharacterEnum::CHUN],
            ['character' => CharacterEnum::ELEESHA],
            ['character' => CharacterEnum::KUAN_TI],
            ['character' => CharacterEnum::FINOLA],
            ['character' => CharacterEnum::FRIEDA],
            ['character' => CharacterEnum::HUA],
            ['character' => CharacterEnum::IAN],
            ['character' => CharacterEnum::JANICE],
            ['character' => CharacterEnum::JIN_SU],
            ['character' => CharacterEnum::DEREK],
            ['character' => CharacterEnum::GIOELE],
            ['character' => CharacterEnum::PAOLA],
            ['character' => CharacterEnum::RALUCA],
            ['character' => CharacterEnum::ROLAND],
            ['character' => CharacterEnum::TERRENCE],
        ];
    }

    private function givenPlayerIsInDaedalus(FunctionalTester $I, string $character): void
    {
        $this->player = $this->addPlayerByCharacter($I, $this->daedalus, $character);
    }

    private function givenPlayerHasLivedCycles(int $cycles): void
    {
        $this->player->getPlayerInfo()->incrementCyclesCount($cycles);
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

    private function thenPlayerStatisticShouldBeIncrementedBy(int $increment, string $character, FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::tryFrom($character),
            userId: $this->player->getUser()->getId()
        );
        $I->assertEquals($increment, $statistic?->getCount());
    }
}
