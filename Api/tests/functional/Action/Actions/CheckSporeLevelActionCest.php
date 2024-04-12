<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\CheckSporeLevel;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CheckSporeLevelActionCest
{
    private CheckSporeLevel $checkSporeLevel;

    public function _before(FunctionalTester $I)
    {
        $this->checkSporeLevel = $I->grabService(CheckSporeLevel::class);
    }

    public function testCheckSporeLevel(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setActionPoint(2)->setSpores(2);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::MYCOSCAN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::MYCOSCAN)
            ->setEquipment($equipmentConfig);
        $I->haveInRepository($gameEquipment);

        $this->checkSporeLevel->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->checkSporeLevel->isVisible());

        $this->checkSporeLevel->execute();

        $I->assertEquals(2, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::CHECK_SPORE_LEVEL,
        ]);
    }
}
