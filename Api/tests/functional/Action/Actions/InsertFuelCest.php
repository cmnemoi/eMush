<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\InsertFuel;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;

class InsertFuelCest
{
    private InsertFuel $insertFuelAction;

    public function _before(FunctionalTester $I)
    {
        $this->insertFuelAction = $I->grabService(InsertFuel::class);
    }

    public function testInsertFuel(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, DaedalusConfigFixtures::class, LocalizationConfigFixtures::class]);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['fuel' => 5, 'cycleStartedAt' => new \DateTime()]);
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
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        $tankTool = new Tool();
        $tankTool->setActions(new ArrayCollection([$action]));
        $I->haveInRepository($tankTool);

        /** @var EquipmentConfig $tankConfig */
        $tankConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'mechanics' => new ArrayCollection([$tankTool])]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($tankConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $capsuleConfig */
        $capsuleConfig = $I->have(EquipmentConfig::class, ['isBreakable' => false, 'name' => ItemEnum::FUEL_CAPSULE]);

        $gameCapsule = new GameItem($player);
        $gameCapsule
            ->setEquipment($capsuleConfig)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameCapsule);

        $this->insertFuelAction->loadParameters($action, $player, $gameCapsule);

        $this->insertFuelAction->execute();

        $I->assertEquals(6, $daedalus->getFuel());
        $I->assertEmpty($player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
    }

    public function testInsertFuelBrokenTank(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, DaedalusConfigFixtures::class, LocalizationConfigFixtures::class]);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['fuel' => 5, 'cycleStartedAt' => new \DateTime()]);
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
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        $tankTool = new Tool();
        $tankTool->setActions(new ArrayCollection([$action]));
        $I->haveInRepository($tankTool);

        /** @var EquipmentConfig $tankConfig */
        $tankConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'mechanics' => new ArrayCollection([$tankTool])]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($tankConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $capsuleConfig */
        $capsuleConfig = $I->have(EquipmentConfig::class, ['isBreakable' => false, 'name' => ItemEnum::FUEL_CAPSULE]);

        $gameCapsule = new GameItem($player);
        $gameCapsule
            ->setEquipment($capsuleConfig)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameCapsule);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->insertFuelAction->loadParameters($action, $player, $gameCapsule);

        $I->assertFalse($this->insertFuelAction->isVisible());
    }
}
