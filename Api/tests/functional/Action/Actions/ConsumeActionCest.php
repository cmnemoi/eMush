<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
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
    private Action $consumeConfig;
    private Consume $consumeAction;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->consumeConfig = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::CONSUME]);
        $this->consumeAction = $I->grabService(Consume::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
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

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
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

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

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

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
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

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

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

        $consumeActionEntity = new Action();
        $consumeActionEntity
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
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

        $this->consumeAction->loadParameters($consumeActionEntity, $player, $gameItem);

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
            action: $this->consumeConfig,
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

        // given I have a standard ration in player inventory
        $ration = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // when Chun consumes the ration
        $this->consumeAction->loadParameters(
            action: $this->consumeConfig,
            player: $this->chun,
            target: $ration,
        );
        $this->consumeAction->execute();

        // then I should not have any starving statuses
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::STARVING_WARNING));
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::STARVING));
    }
}
