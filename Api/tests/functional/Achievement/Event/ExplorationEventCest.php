<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationEventCest extends AbstractExplorationTester
{
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatisticRepositoryInterface $statisticRepository;
    private GameEquipmentServiceInterface $equipmentService;
    private Exploration $exploration;
    private ClosedDaedalus $closedDaedalus;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
        $this->closedDaedalus = $this->daedalus->getDaedalusInfo()->getClosedDaedalus();
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldIncrementExploFeedStatisticWhenReturningWithFood(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithFoodItems($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveExploFeedPendingStatisticWith(2, $I);
    }

    public function shouldNotIncrementExploFeedStatisticWhenReturningWithoutFood(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithoutFood($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveNoExploFeedStatistic($I);
    }

    public function shouldNotIncrementExploFeedStatisticWhenAllExploratorsAreDead(FunctionalTester $I): void
    {
        $this->givenAnExplorationWithFoodAndAllExploratorsDead($I);

        $this->whenExplorationIsClosed();

        $this->thenBothPlayersHaveNoExploFeedStatistic($I);
    }

    public function shouldIncrementMankarogDownStatisticWhenMankarogDefeated(FunctionalTester $I): void
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);

        $this->givenDerekExploresAMankarogHeCanSolo($I, $derek);

        $this->thenDerekAndJaniceHaveMankarogDownStatistic($I, $derek, $janice);
    }

    public function shouldNotIncrementMankarogDownStatisticOnNonMankarogVictory(FunctionalTester $I): void
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);

        $this->givenDerekExploresAnInsectHeCanSolo($I, $derek);

        $this->thenDerekAndJaniceDoNotHaveMankarogDownStatistic($I, $derek, $janice);
    }

    public function shouldIncrementArtefactCollStatisticWhenPlayerFindsArtefactInExploration(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);

        $this->exploration = $this->createExploration($planet, $this->players);

        // given 3 artefacts on the planet
        $this->gameEquipmentService->createGameEquipmentsFromName(
            ItemEnum::STARMAP_FRAGMENT,
            $this->daedalus->getPlanetPlace(),
            quantity: 3,
            reasons: ['exploration'],
            time: new \DateTime(),
        );

        // given a mundane item on the planet
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::PLASTIC_SCRAPS,
            $this->daedalus->getPlanetPlace(),
            ['exploration'],
            new \DateTime()
        );

        // when exploration is closed
        $this->explorationService->closeExploration($this->exploration, ['test']);

        // then all explorators should have artefact coll statistic x3
        $this->thenPlayersArtefactCollPendingStatisticShouldBe(3, $I);
    }

    private function givenAnExplorationWithFoodItems(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);

        $planetPlace = $this->daedalus->getPlanetPlace();
        $this->equipmentService->createGameEquipmentFromName(
            GameRationEnum::ALIEN_STEAK,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
        $this->equipmentService->createGameEquipmentFromName(
            GameRationEnum::COOKED_RATION,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
        $this->equipmentService->createGameEquipmentFromName(
            ItemEnum::STARMAP_FRAGMENT,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );
    }

    private function givenAnExplorationWithoutFood(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);
    }

    private function givenAnExplorationWithFoodAndAllExploratorsDead(FunctionalTester $I): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::OXYGEN], $I);
        $this->exploration = $this->createExploration($planet, $this->players);

        $planetPlace = $this->daedalus->getPlanetPlace();
        $this->equipmentService->createGameEquipmentFromName(
            GameRationEnum::ALIEN_STEAK,
            $planetPlace,
            ['exploration'],
            new \DateTime()
        );

        $this->player1->kill();
        $this->player2->kill();
    }

    private function givenDerekExploresAMankarogHeCanSolo(FunctionalTester $I, Player $derek): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::MANKAROG], $I);

        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::MANKAROG,
            events: [PlanetSectorEvent::FIGHT_32 => 1],
        );

        $this->equipmentService->createGameEquipmentFromName(
            ItemEnum::BLASTER,
            $derek,
            [],
            new \DateTime()
        )->getWeaponMechanicOrThrow()->setExpeditionBonus(99);

        $this->equipmentService->createGameEquipmentFromName(
            GearItemEnum::SPACESUIT,
            $derek,
            [],
            new \DateTime()
        );

        $this->exploration = $this->createExploration($planet, new ArrayCollection([$derek]));
        $this->explorationService->dispatchExplorationEvent($this->exploration);
    }

    private function givenDerekExploresAnInsectHeCanSolo(FunctionalTester $I, Player $derek): void
    {
        $planet = $this->createPlanet([PlanetSectorEnum::INSECT], $I);

        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INSECT,
            events: [PlanetSectorEvent::FIGHT_10 => 1],
        );

        $this->equipmentService->createGameEquipmentFromName(
            ItemEnum::BLASTER,
            $derek,
            [],
            new \DateTime()
        )->getWeaponMechanicOrThrow()->setExpeditionBonus(99);

        $this->equipmentService->createGameEquipmentFromName(
            GearItemEnum::SPACESUIT,
            $derek,
            [],
            new \DateTime()
        );

        $this->exploration = $this->createExploration($planet, new ArrayCollection([$derek]));
        $this->explorationService->dispatchExplorationEvent($this->exploration);
    }

    private function whenExplorationIsClosed(): void
    {
        $this->explorationService->closeExploration($this->exploration, ['test']);
    }

    private function thenBothPlayersHaveExploFeedPendingStatisticWith(int $count, FunctionalTester $I): void
    {
        $player1PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId(), $this->closedDaedalus->getId());
        $player2PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId(), $this->closedDaedalus->getId());
        $player1Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId());
        $player2Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId());

        $I->assertNotNull($player1PendingStatistic);
        $I->assertNotNull($player2PendingStatistic);
        $I->assertNull($player1Statistic);
        $I->assertNull($player2Statistic);
        $I->assertEquals($count, $player1PendingStatistic->getCount());
        $I->assertEquals($count, $player2PendingStatistic->getCount());
    }

    private function thenBothPlayersHaveNoExploFeedStatistic(FunctionalTester $I): void
    {
        $player1PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId(), $this->closedDaedalus->getId());
        $player2PendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId(), $this->closedDaedalus->getId());
        $player1Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player1->getUser()->getId());
        $player2Statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::EXPLO_FEED, $this->player2->getUser()->getId());

        $I->assertNull($player1PendingStatistic);
        $I->assertNull($player2PendingStatistic);
        $I->assertNull($player1Statistic);
        $I->assertNull($player2Statistic);
    }

    private function thenDerekAndJaniceHaveMankarogDownStatistic(FunctionalTester $I, Player $derek, Player $janice): void
    {
        $derekPendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::MANKAROG_DOWN, $derek->getUser()->getId(), $this->closedDaedalus->getId());
        $janicePendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::MANKAROG_DOWN, $janice->getUser()->getId(), $this->closedDaedalus->getId());
        $derekStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::MANKAROG_DOWN, $derek->getUser()->getId());
        $janiceStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::MANKAROG_DOWN, $janice->getUser()->getId());

        $I->assertNotNull($derekPendingStatistic);
        $I->assertNotNull($janicePendingStatistic);
        $I->assertNull($derekStatistic);
        $I->assertNull($janiceStatistic);
        $I->assertEquals(1, $derekPendingStatistic->getCount());
        $I->assertEquals(1, $janicePendingStatistic->getCount());
    }

    private function thenDerekAndJaniceDoNotHaveMankarogDownStatistic(FunctionalTester $I, Player $derek, Player $janice): void
    {
        $derekPendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::MANKAROG_DOWN, $derek->getUser()->getId(), $this->closedDaedalus->getId());
        $janicePendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::MANKAROG_DOWN, $janice->getUser()->getId(), $this->closedDaedalus->getId());
        $derekStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::MANKAROG_DOWN, $derek->getUser()->getId());
        $janiceStatistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::MANKAROG_DOWN, $janice->getUser()->getId());

        $I->assertNull($derekPendingStatistic);
        $I->assertNull($janicePendingStatistic);
        $I->assertNull($derekStatistic);
        $I->assertNull($janiceStatistic);
    }

    private function thenPlayersArtefactCollPendingStatisticShouldBe(int $expected, FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::ARTEFACT_COLL,
                $player->getUser()->getId(),
                $this->closedDaedalus->getId(),
            );

            $I->assertEquals(
                expected: [
                    'name' => StatisticEnum::ARTEFACT_COLL,
                    'count' => $expected,
                    'userId' => $player->getUser()->getId(),
                    'closedDaedalusId' => $this->closedDaedalus->getId(),
                    'isRare' => false,
                ],
                actual: $statistic?->toArray(),
                message: 'Player ' . $player->getLogName() . ' artefact coll statistic should be ' . $expected,
            );
        }
    }
}
