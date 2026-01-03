<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\ExchangeBody;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FinishDaedalusEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private PlayerServiceInterface $playerService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    #[DataProvider('characterDataProvider')]
    public function shouldIncrementCharacterStatisticWhenDaedalusEnds(FunctionalTester $I, Example $example): void
    {
        $this->givenPlayerIsInDaedalus($I, $example['character']);
        $this->givenPlayerHasLivedCycles(5);

        $this->whenDaedalusEndsWith(EndCauseEnum::SUPER_NOVA);

        $this->thenPlayerStatisticShouldBeIncrementedBy(5, $example['character'], $I);
    }

    public function shouldIncrementMushCyclesStatisticWhenDaedalusEnds(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player);
        $this->givenPlayerHasLivedCycles(5);

        $this->whenDaedalusEndsWith(EndCauseEnum::SUPER_NOVA);

        $I->assertNull($this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::from($this->player->getName()),
            userId: $this->player->getUser()->getId()
        )?->getId());
        $this->thenPlayerMushCyclesStatisticShouldBeIncrementedBy(5, $I);
    }

    public function shouldIncrementMultipleCharactersCyclesAndMushCyclesStatisticAfterTransfer(FunctionalTester $I): void
    {
        $user1 = $this->chun->getUser();
        $user2 = $this->kuanTi->getUser();

        // given user1 has 2 cycles as chun
        $this->givenPlayerHasLivedCycles(2, $this->chun);

        // given user2 has 5 cycles as kuan ti
        $this->givenPlayerHasLivedCycles(5, $this->kuanTi);

        // given user 2 is now mush
        $this->convertPlayerToMush($I, $this->kuanTi);

        // given user 2 has 2 cycles as mush
        $this->givenPlayerHasLivedCycles(2, $this->kuanTi);

        // given kuan Ti transfers into chun
        $this->givenPlayerTransfersInto($this->kuanTi, $this->chun, $I);

        // given user1 has 2 cycles as kuan ti
        $this->givenPlayerHasLivedCycles(2, $this->kuanTi);

        // given user2 has 2 cycles as mush
        $this->givenPlayerHasLivedCycles(2, $this->chun);

        $this->whenDaedalusEndsWith(EndCauseEnum::SUPER_NOVA);

        // then user1 should have 2 cycles as chun
        $I->assertEquals(
            expected: 2,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                name: StatisticEnum::CHUN,
                userId: $user1->getId()
            )?->getCount(),
            message: 'user1 should have 2 cycles as chun'
        );

        // then user1 should have 2 cycles as kuan ti
        $I->assertEquals(
            expected: 2,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                name: StatisticEnum::KUAN_TI,
                userId: $user1->getId()
            )?->getCount(),
            message: 'user1 should have 2 cycles as kuan ti'
        );

        // then user2 should have 5 cycles as kuan ti
        $I->assertEquals(
            expected: 5,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                name: StatisticEnum::KUAN_TI,
                userId: $user2->getId()
            )?->getCount(),
            message: 'user2 should have 5 cycles as kuan ti'
        );

        // then user2 should have 2 cycles as mush
        $I->assertEquals(
            expected: 4,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                name: StatisticEnum::MUSH_CYCLES,
                userId: $user2->getId()
            )?->getCount(),
            message: 'user2 should have 2 cycles as mush'
        );
    }

    public function shouldClearPendingStatisticsAfterDaedalusEnd(FunctionalTester $I): void
    {
        // given user1 has 2 cycles as chun
        $this->givenPlayerHasLivedCycles(2, $this->chun);

        $this->thenChunHasXCyclesPendingStatistic(2, $I);

        $this->whenDaedalusEndsWith(EndCauseEnum::SUPER_NOVA);

        $this->thenChunHasNoPendingStatistics($I);
    }

    public function shouldNotIncrementDeprecatedCycleCount(FunctionalTester $I): void
    {
        // given user1 has 2 cycles as chun
        $this->givenPlayerHasLivedCycles(2, $this->chun);

        $I->assertEquals(0, $this->chun->getUser()->getCycleCounts()->getForCharacter(CharacterEnum::CHUN));
    }

    #[DataProvider('shipEndCauseDataProvider')]
    public function shouldGiveTriumphOnDaedalusEnd(FunctionalTester $I, Example $example): void
    {
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->convertPlayerToMush($I, $this->kuanTi);

        $this->whenDaedalusEndsWith($example['shipEndCause']);

        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->chun, $I);
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->kuanTi, $I);
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($gioele, $I);
    }

    public function shouldGiveTriumphWhenShipEndsOnCycleChange(FunctionalTester $I): void
    {
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->givenPlayerDies($this->kuanTi);
        $paola->setMoralPoint(0); // given Paola dies to depression next cycle
        // given Chun dies to asphyxia next cycle
        $this->givenPlayerHasOxygenCapsule($paola);
        $this->daedalus->setOxygen(0);

        $this->whenCycleIsAdvancedForDaedalus();

        $I->assertTrue($this->daedalus->getDaedalusInfo()->isDaedalusFinished());
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->chun, $I);
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->kuanTi, $I);
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($paola, $I);
    }

    public function shouldGiveTriumphOnNeronAssassination(FunctionalTester $I): void
    {
        // Given all players converted to Mush
        $this->convertPlayerToMush($I, $this->player);
        $this->convertPlayerToMush($I, $this->player2);

        $this->whenCycleIsAdvancedForDaedalus();

        $I->assertTrue($this->daedalus->getDaedalusInfo()->isDaedalusFinished());
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->player, $I);
        $this->thenTriumphStatisticShouldEqualPlayerTriumphFor($this->player2, $I);
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

    protected function shipEndCauseDataProvider(): array
    {
        return [
            ['shipEndCause' => EndCauseEnum::SOL_RETURN],
            ['shipEndCause' => EndCauseEnum::EDEN],
            ['shipEndCause' => EndCauseEnum::DAEDALUS_DESTROYED],
            ['shipEndCause' => EndCauseEnum::SUPER_NOVA],
        ];
    }

    private function givenPlayerTransfersInto(Player $player, Player $target, FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TRANSFER, $I, $player);

        $target->setSpores(1);

        /** @var ExchangeBody $exchangeBody */
        $exchangeBody = $I->grabService(ExchangeBody::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXCHANGE_BODY]);

        $exchangeBody->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $player,
            player: $player,
            target: $target,
        );
        $exchangeBody->execute();
    }

    private function givenPlayerIsInDaedalus(FunctionalTester $I, string $character): void
    {
        $this->player = $this->addPlayerByCharacter($I, $this->daedalus, $character);
    }

    private function givenPlayerHasLivedCycles(int $cycles, ?Player $player = null): void
    {
        $player ??= $this->player;

        for ($i = 0; $i < $cycles; ++$i) {
            $cycleEvent = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], new \DateTime());
            $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }
    }

    private function givenPlayerDies(Player $player): void
    {
        $this->playerService->killPlayer($player, EndCauseEnum::INJURY);
    }

    private function givenPlayerHasOxygenCapsule(Player $player): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::OXYGEN_CAPSULE,
            $player,
            ['test'],
            new \DateTime()
        );
    }

    private function whenDaedalusEndsWith(string $endCause): void
    {
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            [$endCause],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
    }

    private function whenCycleIsAdvancedForDaedalus(): void
    {
        $daedalusCycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($daedalusCycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenPlayerStatisticShouldBeIncrementedBy(int $increment, string $character, FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::tryFrom($character),
            userId: $this->player->getUser()->getId()
        );
        $I->assertEquals($increment, $statistic?->getCount());
    }

    private function thenPlayerMushCyclesStatisticShouldBeIncrementedBy(int $increment, FunctionalTester $I): void
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::MUSH_CYCLES,
            userId: $this->player->getUser()->getId()
        );
        $I->assertEquals($increment, $statistic?->getCount());
    }

    private function thenChunHasXCyclesPendingStatistic(int $expectedValue, FunctionalTester $I): void
    {
        $chunCyclesPendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::CHUN,
            userId: $this->chun->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )?->getCount();
        $I->assertEquals(
            $expectedValue,
            $chunCyclesPendingStatistic,
            "Chun should have {$expectedValue} cycles pending statistics, got {$chunCyclesPendingStatistic}"
        );
    }

    private function thenChunHasNoPendingStatistics(FunctionalTester $I): void
    {
        $chunCyclesPendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::CHUN,
            userId: $this->chun->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )?->getCount();
        $I->assertNull($chunCyclesPendingStatistic, "Chun should have no cycles pending statistics, got {$chunCyclesPendingStatistic}");
    }

    private function thenTriumphStatisticShouldEqualPlayerTriumphFor(Player $player, FunctionalTester $I): void
    {
        $triumphOnEndPage = $player->getPlayerInfo()->getClosedPlayer()->getTriumph();
        $tirumphInStatistics = $this->statisticRepository->findByNameAndUserIdOrNull(
            name: StatisticEnum::TRIUMPH,
            userId: $player->getUser()->getId()
        )?->getCount();

        $I->assertEquals(
            expected: $triumphOnEndPage,
            actual: $tirumphInStatistics,
            message: "{$player->getLogName()} with {$triumphOnEndPage} triumph has {$tirumphInStatistics} triumph in statistics",
        );
    }
}
