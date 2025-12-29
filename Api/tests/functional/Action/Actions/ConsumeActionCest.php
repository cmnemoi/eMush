<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ConsumeActionCest extends AbstractFunctionalTest
{
    private ActionConfig $consumeConfig;
    private Consume $consumeAction;

    private AddSkillToPlayerService $addSkillToPlayer;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    private Player $contaminator;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->consumeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CONSUME]);
        $this->consumeAction = $I->grabService(Consume::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->contaminator = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::STEPHEN);
        $this->convertPlayerToMush($I, $this->contaminator);
    }

    public function testConsume(FunctionalTester $I)
    {
        // given kuan ti has those values
        $this->kuanTi
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5);
        $I->haveInRepository($this->kuanTi);

        $this->givenKuanTiHasANonStandardRation($I);

        $this->whenKuanTiConsumesTheRation();

        $I->assertEquals(1, $this->kuanTi->getSatiety(), 'Kuan Ti should have 1 satiety, not ' . $this->kuanTi->getSatiety());
        $I->assertEquals(7, $this->kuanTi->getActionPoint(), 'Kuan Ti should have 7 action points, not ' . $this->kuanTi->getActionPoint());
        $I->assertEquals(8, $this->kuanTi->getMovementPoint(), 'Kuan Ti should have 8 movement points, not ' . $this->kuanTi->getMovementPoint());
        $I->assertEquals(9, $this->kuanTi->getMoralPoint(), 'Kuan Ti should have 9 moral points, not ' . $this->kuanTi->getMoralPoint());
        $I->assertEquals(10, $this->kuanTi->getHealthPoint(), 'Kuan Ti should have 10 health points, not ' . $this->kuanTi->getHealthPoint());
        $I->assertFalse($this->kuanTi->hasEquipmentByName(GameRationEnum::STANDARD_RATION), 'Kuan Ti should not have the standard ration');
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::STARVING), 'Kuan Ti should not have the starving status');
    }

    public function testConsumeWithNegativeSatiety(FunctionalTester $I)
    {
        // given kuan ti has those values
        $this->kuanTi
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5)
            ->setSatiety(-7);
        $I->haveInRepository($this->kuanTi);

        $this->givenKuanTiHasANonStandardRation($I);

        $this->whenKuanTiConsumesTheRation();

        $I->assertEquals(1, $this->kuanTi->getSatiety(), 'Kuan Ti should have 1 satiety, not ' . $this->kuanTi->getSatiety());
        $I->assertEquals(7, $this->kuanTi->getActionPoint(), 'Kuan Ti should have 7 action points, not ' . $this->kuanTi->getActionPoint());
        $I->assertEquals(8, $this->kuanTi->getMovementPoint(), 'Kuan Ti should have 8 movement points, not ' . $this->kuanTi->getMovementPoint());
        $I->assertEquals(9, $this->kuanTi->getMoralPoint(), 'Kuan Ti should have 9 moral points, not ' . $this->kuanTi->getMoralPoint());
        $I->assertEquals(10, $this->kuanTi->getHealthPoint(), 'Kuan Ti should have 10 health points, not ' . $this->kuanTi->getHealthPoint());
        $I->assertFalse($this->kuanTi->hasEquipmentByName(GameRationEnum::STANDARD_RATION), 'Kuan Ti should not have the standard ration');
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::STARVING), 'Kuan Ti should not have the starving status');
    }

    public function testMushConsume(FunctionalTester $I)
    {
        $this->givenKuanTiIsMush();
        $this->kuanTi
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5)
            ->setSatiety(-7);

        $this->givenKuanTiHasANonStandardRation($I);

        $this->whenKuanTiConsumesTheRation();

        $I->assertEquals(5, $this->kuanTi->getActionPoint(), 'Mush Kuan Ti should have 5 action points, not ' . $this->kuanTi->getActionPoint());
        $I->assertEquals(5, $this->kuanTi->getMovementPoint(), 'Mush Kuan Ti should have 5 movement points, not ' . $this->kuanTi->getMovementPoint());
        $I->assertEquals(5, $this->kuanTi->getMoralPoint(), 'Mush Kuan Ti should have 5 moral points, not ' . $this->kuanTi->getMoralPoint());
        $I->assertEquals(5, $this->kuanTi->getHealthPoint(), 'Mush Kuan Ti should have 5 health points, not ' . $this->kuanTi->getHealthPoint());
        $I->assertEquals(1, $this->kuanTi->getSatiety(), 'Mush Kuan Ti should have 1 satiety, not ' . $this->kuanTi->getSatiety());
        $I->assertFalse($this->kuanTi->hasEquipmentByName(GameRationEnum::STANDARD_RATION), 'Kuan Ti should not have the standard ration');
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::STARVING), 'Kuan Ti should not have the starving status');
    }

    public function testMushConsumePrintsASpecificLog(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();

        $this->givenKuanTiHasARation();

        $this->whenKuanTiConsumesTheRation();

        // then I should see a specific log
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::CONSUME_MUSH,
            ]
        );
    }

    public function shouldMakeStarvingStatusesDisappear(FunctionalTester $I): void
    {
        // given Kuan Ti is starving
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::STARVING_WARNING,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        $this->givenKuanTiHasARation();

        $this->whenKuanTiConsumesTheRation();

        // then Kuan Ti should not have any starving statuses
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::STARVING_WARNING), 'Kuan Ti should not have the starving warning status');
        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::STARVING), 'Kuan Ti should not have the starving status');
    }

    public function caffeineJunkieShouldNotGainMoreActionPointsWithARation(FunctionalTester $I): void
    {
        // given Kuan Ti is a caffeine junkie
        $this->addSkillToPlayer->execute(SkillEnum::CAFFEINE_JUNKIE, $this->kuanTi);

        $this->givenKuanTiHasARation();

        // given Kuan Ti has 6 action points
        $this->kuanTi->setActionPoint(6);

        $this->whenKuanTiConsumesTheRation();

        // then Kuan Ti should have 10 action points
        $I->assertEquals(10, $this->kuanTi->getActionPoint(), 'Kuan Ti should have 10 action points, not ' . $this->kuanTi->getActionPoint());
    }

    public function frugivoreShouldGainMoreActionPointsWithAlienFruits(FunctionalTester $I): void
    {
        // given Kuan Ti is a frugivore
        $this->addSkillToPlayer->execute(SkillEnum::FRUGIVORE, $this->kuanTi);

        $this->givenKuanTiHasKubinus();

        // given Kuan Ti has 6 action points
        $this->kuanTi->setActionPoint(6);

        $this->whenKuanTiConsumesTheKubinus();

        // then Kuan Ti should have 6 (base) + 1 (alien fruit) + 2 (frugivore bonus) action points
        $I->assertEquals(6 + 1 + 2, $this->kuanTi->getActionPoint(), 'Kuan Ti should have 9 action points, not ' . $this->kuanTi->getActionPoint());
    }

    public function frugivoreShouldGainMoreActionPointsWithBanana(FunctionalTester $I): void
    {
        // given Kuan Ti is a frugivore
        $this->addSkillToPlayer->execute(SkillEnum::FRUGIVORE, $this->kuanTi);

        // given Kuan Ti has banana in his inventory
        $banana = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        // given Kuan Ti has 6 action points
        $this->kuanTi->setActionPoint(6);

        // when Kuan Ti consumes the banana
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $banana,
            player: $this->kuanTi,
            target: $banana,
        );
        $this->consumeAction->execute();

        // then Kuan Ti should have 6 (base) + 1 (banana) + 1 (frugivore bonus) action points
        $I->assertEquals(6 + 1 + 1, $this->kuanTi->getActionPoint(), 'Kuan Ti should have 8 action points, not ' . $this->kuanTi->getActionPoint());
    }

    public function contaminatedFoodShouldContaminateHumanPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAContaminatedRationWithSpores(1);

        $this->whenKuanTiConsumesTheRation();

        $this->thenKuanTiShouldBeContaminatedBySpores(1, $I);
    }

    public function contaminatedFoodShouldNotContaminateMushPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();

        $this->givenKuanTiHasAContaminatedRationWithSpores(1);

        $this->whenKuanTiConsumesTheRation();

        $this->thenKuanTiShouldBeContaminatedBySpores(0, $I);
    }

    public function contaminatedFoodShouldCreateAMessageInMushChannel(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAContaminatedRationWithSpores(1);

        $this->whenKuanTiConsumesTheRation();

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'channel' => $this->mushChannel,
                'message' => MushMessageEnum::INFECT_TRAPPED_RATION,
            ]
        );
    }

    public function shouldGiveTenMushTriumphToContaminatorWhenHumanEatsSevenSpores(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAContaminatedRationWithSpores(7);

        $this->givenContaminatorhasTriumph(0, $I);

        $this->whenKuanTiConsumesTheRation();

        $this->thenContaminatorShouldHaveTriumph(10, $I); // +1 infection, +1 infection, +8 conversion
    }

    public function shouldImproveTimesEatenStatisticWhenEatingContaminatedRation(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAContaminatedRationWithSpores(4);

        $this->whenKuanTiConsumesTheRation();

        $I->assertEquals(1, $this->kuanTi->getPlayerInfo()->getStatistics()->getTimesEaten());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getStatistics()->getDrugsTaken());
    }

    public function shouldImproveTimesEatenStatisticWhenEatingFruit(FunctionalTester $I): void
    {
        $this->givenKuanTiHasKubinus();

        $this->whenKuanTiConsumesTheKubinus();

        $I->assertEquals(1, $this->kuanTi->getPlayerInfo()->getStatistics()->getTimesEaten());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getStatistics()->getDrugsTaken());
    }

    public function shouldIncrementCookedTakenPendingStatisticWhenEatingCookedRation(FunctionalTester $I): void
    {
        $this->givenKuanTiHasFood(GameRationEnum::COOKED_RATION);

        $this->whenKuanTiConsumesFood(GameRationEnum::COOKED_RATION);

        $I->assertEquals(1, $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::COOKED_TAKEN,
            userId: $this->kuanTi->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )->getCount());
    }

    public function shouldNotIncrementCookedTakenPendingStatisticWhenEatingNonCookedRation(FunctionalTester $I): void
    {
        $this->givenKuanTiHasFood(GameRationEnum::STANDARD_RATION);

        $this->whenKuanTiConsumesFood(GameRationEnum::STANDARD_RATION);

        $I->assertNull($this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::COOKED_TAKEN,
            userId: $this->kuanTi->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        ));
    }

    public function shouldIncrementCoffeeTakenStatisticWhenEatingCoffee(FunctionalTester $I): void
    {
        $this->givenKuanTiHasFood(GameRationEnum::COFFEE);

        $this->whenKuanTiConsumesFood(GameRationEnum::COFFEE);

        $I->assertEquals(1, $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: StatisticEnum::COFFEE_MAN,
            userId: $this->kuanTi->getUser()->getId(),
            closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        )?->getCount());
    }

    private function givenKuanTiHasAContaminatedRationWithSpores(int $spores): void
    {
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        for ($i = 0; $i < $spores; ++$i) {
            $this->statusService->createOrIncrementChargeStatus(
                name: EquipmentStatusEnum::CONTAMINATED,
                holder: $ration,
                target: $this->contaminator,
            );
        }
    }

    private function givenKuanTiHasARation(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasANonStandardRation(FunctionalTester $I): GameItem
    {
        $consumeActionEntity = new ActionConfig();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($consumeActionEntity);

        $ration = new Ration();
        $ration
            ->setActions(new ArrayCollection([$consumeActionEntity]))
            ->setName(GameRationEnum::STANDARD_RATION . '_' . GameConfigEnum::TEST);
        $I->haveInRepository($ration);

        // set the effect you need
        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($this->daedalus)
            ->setRation($ration);
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'name' => GameRationEnum::STANDARD_RATION,
        ]);

        $I->haveInRepository($equipmentConfig);

        $this->daedalus->getGameConfig()->addEquipmentConfig($equipmentConfig);
        $I->haveInRepository($this->daedalus->getGameConfig());

        // create the ration and give it to kuan ti
        $gameItem = new GameItem($this->kuanTi);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName(GameRationEnum::STANDARD_RATION);
        $I->haveInRepository($gameItem);

        return $gameItem;
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenContaminatorhasTriumph(int $quantity, FunctionalTester $I): void
    {
        $this->contaminator->setTriumph($quantity);
        $I->haveInRepository($this->contaminator);
    }

    private function givenKuanTiHasKubinus(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::KUBINUS,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiHasFood(string $food): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $food,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenKuanTiConsumesTheRation(): void
    {
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $this->kuanTi->getEquipmentByName(GameRationEnum::STANDARD_RATION),
            player: $this->kuanTi,
            target: $this->kuanTi->getEquipmentByName(GameRationEnum::STANDARD_RATION),
        );
        $this->consumeAction->execute();
    }

    private function whenKuanTiConsumesTheKubinus(): void
    {
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $this->kuanTi->getEquipmentByName(GameFruitEnum::KUBINUS),
            player: $this->kuanTi,
            target: $this->kuanTi->getEquipmentByName(GameFruitEnum::KUBINUS),
        );
        $this->consumeAction->execute();
    }

    private function whenKuanTiConsumesFood(string $food): void
    {
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $this->kuanTi->getEquipmentByName($food),
            player: $this->kuanTi,
            target: $this->kuanTi->getEquipmentByName($food),
        );
        $this->consumeAction->execute();
    }

    private function thenKuanTiShouldBeContaminatedBySpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->kuanTi->getSpores());
    }

    private function thenContaminatorShouldHaveTriumph(int $triumph, FunctionalTester $I): void
    {
        $I->assertEquals($triumph, $this->contaminator->getTriumph());
    }
}
