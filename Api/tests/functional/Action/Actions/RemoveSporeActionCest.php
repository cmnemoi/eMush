<?php

namespace Mush\Tests\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\RemoveSpore;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
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
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;

class RemoveSporeActionCest
{
    private RemoveSpore $removeSpore;

    public function _before(FunctionalTester $I)
    {
        $this->removeSpore = $I->grabService(removeSpore::class);
    }

    public function testRemoveSpore(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($attemptConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig]))
        ;
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
        $player
            ->setActionPoint(2)
            ->setHealthPoint(9)
            ->setSpores(1)
        ;
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::REMOVE_SPORE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $itemConfig
            ->setEquipmentName(ToolItemEnum::SPORE_SUCKER)
            ->setActions(new ArrayCollection([$action]))
        ;

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ToolItemEnum::SPORE_SUCKER)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $this->removeSpore->loadParameters($action, $player, $gameItem);

        $I->assertTrue($this->removeSpore->isVisible());

        $this->removeSpore->execute();

        $I->assertEquals(1, $player->getActionPoint());
        $I->assertEquals(6, $player->getHealthPoint());
        $I->assertEquals(0, $player->getSpores());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::REMOVE_SPORE_SUCCESS,
        ]);

        // Check that we get a fail if we execute when there are no spores
        $this->removeSpore->execute();

        $I->assertEquals(0, $player->getActionPoint());
        $I->assertEquals(3, $player->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::REMOVE_SPORE_FAIL,
        ]);
    }
}
