<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

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

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->consumeConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CONSUME]);
        $this->consumeAction = $I->grabService(Consume::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testConsume(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

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

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration);
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'name' => GameRationEnum::STANDARD_RATION,
        ]);

        $I->haveInRepository($equipmentConfig);

        $gameConfig->addEquipmentConfig($equipmentConfig);
        $I->refreshEntities($gameConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration');
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters(
            actionConfig: $consumeActionEntity,
            actionProvider: $gameItem,
            player: $player,
            target: $gameItem
        );

        $this->consumeAction->execute();

        $I->assertEquals(1, $player->getSatiety());
        $I->assertEquals(0, $player->getStatuses()->count());
        $I->assertEquals(7, $player->getActionPoint());
        $I->assertEquals(8, $player->getMovementPoint());
        $I->assertEquals(9, $player->getMoralPoint());
        $I->assertEquals(10, $player->getHealthPoint());

        $I->assertEquals(0, $room->getEquipments()->count());
    }

    public function testConsumeWithNegativeSatiety(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5)
            ->setSatiety(-7);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

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

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setRation($ration);
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'place' => $room,
            'name' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setEquipmentName('ration');

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration');
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters(
            actionConfig: $consumeActionEntity,
            actionProvider: $gameItem,
            player: $player,
            target: $gameItem
        );

        $this->consumeAction->execute();

        $I->assertEquals(1, $player->getSatiety());
        $I->assertEquals(0, $player->getStatuses()->count());
        $I->assertEquals(7, $player->getActionPoint());
        $I->assertEquals(8, $player->getMovementPoint());
        $I->assertEquals(9, $player->getMoralPoint());
        $I->assertEquals(10, $player->getHealthPoint());

        $I->assertEquals(0, $room->getEquipments()->count());
    }

    public function testMushConsume(FunctionalTester $I)
    {
        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);

        $fullStomach = new StatusConfig();
        $fullStomach
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fullStomach);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$mushConfig, $fullStomach]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(5)
            ->setHealthPoint(5)
            ->setMoralPoint(5)
            ->setMovementPoint(5);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $mushStatus = new Status($player, $mushConfig);
        $I->haveInRepository($mushStatus);

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

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setDaedalus($daedalus)
            ->setRation($ration);
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'place' => $room,
            'name' => 'ration',
        ]);

        $equipmentConfig
            ->setMechanics(new ArrayCollection([$ration]))
            ->setEquipmentName('ration');

        $I->haveInRepository($equipmentConfig);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration');
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters(
            actionConfig: $consumeActionEntity,
            actionProvider: $gameItem,
            player: $player,
            target: $gameItem
        );

        $this->consumeAction->execute();

        $I->assertEquals(4, $player->getSatiety());
        $I->assertCount(2, $player->getStatuses());

        $I->assertEquals(0, $room->getEquipments()->count());
    }

    public function testMushConsumePrintsASpecificLog(FunctionalTester $I): void
    {
        // given I have a Mush player
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // given I have a standard ration in player inventory
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player consumes the ration
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $ration,
            player: $this->player,
            target: $ration,
        );
        $this->consumeAction->execute();

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
        // given Chun is starving
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::STARVING_WARNING,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has a standard ration in her inventory
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when Chun consumes the ration
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $ration,
            player: $this->chun,
            target: $ration,
        );
        $this->consumeAction->execute();

        // then Chun should not have any starving statuses
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::STARVING_WARNING));
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::STARVING));
    }

    public function caffeineJunkieShouldNotGainMoreActionPointsWithARation(FunctionalTester $I): void
    {
        // given Chun is a caffeine junkie
        $this->addSkillToPlayer->execute(SkillEnum::CAFFEINE_JUNKIE, $this->chun);

        // given Chun has a standard ration in her inventory
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has 6 action points
        $this->chun->setActionPoint(6);

        // when Chun consumes the ration
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $ration,
            player: $this->chun,
            target: $ration,
        );
        $this->consumeAction->execute();

        // then Chun should have 10 action points
        $I->assertEquals(10, $this->chun->getActionPoint());
    }

    public function frugivoreShouldGainMoreActionPointsWithAlienFruits(FunctionalTester $I): void
    {
        // given Chun is a frugivore
        $this->addSkillToPlayer->execute(SkillEnum::FRUGIVORE, $this->chun);

        // given Chun has alien fruits in her inventory
        $alienFruit = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::ANEMOLE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has 6 action points
        $this->chun->setActionPoint(6);

        // when Chun consumes the alien fruits
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $alienFruit,
            player: $this->chun,
            target: $alienFruit,
        );
        $this->consumeAction->execute();

        // then Chun should have 6 (base) + 1 (alien fruit) + 2 (frugivore bonus) action points
        $I->assertEquals(6 + 1 + 2, $this->chun->getActionPoint());
    }

    public function frugivoreShouldGainMoreActionPointsWithBanana(FunctionalTester $I): void
    {
        // given Chun is a frugivore
        $this->addSkillToPlayer->execute(SkillEnum::FRUGIVORE, $this->chun);

        // given Chun has alien fruits in her inventory
        $alienFruit = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameFruitEnum::BANANA,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has 6 action points
        $this->chun->setActionPoint(6);

        // when Chun consumes the alien fruits
        $this->consumeAction->loadParameters(
            actionConfig: $this->consumeConfig,
            actionProvider: $alienFruit,
            player: $this->chun,
            target: $alienFruit,
        );
        $this->consumeAction->execute();

        // then Chun should have 6 (base) + 1 (alien fruit) + 1 (frugivore bonus) action points
        $I->assertEquals(6 + 1 + 1, $this->chun->getActionPoint());
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
                target: $this->chun,
            );
        }
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

    private function thenKuanTiShouldBeContaminatedBySpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->kuanTi->getSpores());
    }
}
