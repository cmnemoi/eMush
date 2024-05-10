<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class AttemptActionChangeCest
{
    private Repair $repairAction;
    private Disassemble $disassembleAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
        $this->disassembleAction = $I->grabService(Disassemble::class);
    }

    public function testChangeAttemptAction(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]));
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
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);

        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionRepair = new ActionConfig();
        $actionRepair
            ->setName(ActionEnum::REPAIR->value)
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($actionRepair);

        $actionDisassemble = new ActionConfig();
        $actionDisassemble
            ->setName(ActionEnum::DISASSEMBLE->value)
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($actionDisassemble);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActionConfigs(new ArrayCollection([$actionDisassemble, $actionRepair]));

        $gameEquipment = new GameItem($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->repairAction->loadParameters($actionRepair,$gameEquipment, $player, $gameEquipment);

        // Execute repair
        $this->repairAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);

        // Execute repair a second time
        $this->repairAction->execute();

        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $gameEquipment, $player, $gameEquipment);

        // Now execute the other action
        $this->disassembleAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::DISASSEMBLE, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $gameEquipment, $player, $gameEquipment);
        $this->disassembleAction->execute();
        $I->assertEquals(ActionEnum::DISASSEMBLE, $player->getStatuses()->first()->getAction());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
    }

    public function testSuccessRateIsCorrectlyCapped(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]));
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
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);

        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionRepair = new ActionConfig();
        $actionRepair
            ->setName(ActionEnum::REPAIR->value)
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($actionRepair);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActionConfigs(new ArrayCollection([$actionRepair]));

        $gameEquipment = new GameItem($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);
        $I->assertEquals(0, $this->repairAction->getSuccessRate());

        // Execute repair
        $this->repairAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);

        // Execute repair a second and third time
        $this->repairAction->execute();
        $this->repairAction->execute();

        // now up the success chances
        $actionRepair->setSuccessRate(80);
        $I->flushToDatabase($actionRepair);
        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);

        $I->assertEquals(99, $this->repairAction->getSuccessRate());
    }

    public function testNormalizeAnotherAction(FunctionalTester $I)
    {
        $attemptConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => StatusEnum::ATTEMPT]);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]));
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        $player->setPlayerVariables($characterConfig);
        $player->setActionPoint(10);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionRepair = new ActionConfig();
        $actionRepair
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(1)
            ->setSuccessRate(0)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($actionRepair);

        $actionDisassemble = new ActionConfig();
        $actionDisassemble
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setActionCost(1)
            ->setSuccessRate(75)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($actionDisassemble);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActionConfigs(new ArrayCollection([$actionDisassemble, $actionRepair]));

        $gameEquipment = new GameItem($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);

        // Execute repair
        $this->repairAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->repairAction->loadParameters($actionRepair, $gameEquipment, $player, $gameEquipment);
        // Execute repair a second time
        $this->repairAction->execute();
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $gameEquipment, $player, $gameEquipment);

        $I->assertEquals(75, $this->disassembleAction->getSuccessRate());

        // check that the attempt status is still correctly set to repair
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
    }
}
