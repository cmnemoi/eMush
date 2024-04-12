<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class PublicBroadcastActionCest
{
    private PublicBroadcast $publicBroadcastAction;
    private Action $action;
    private StatusConfig $watchedPublicBroadcastStatus;

    public function _before(FunctionalTester $I)
    {
        $this->publicBroadcastAction = $I->grabService(PublicBroadcast::class);
        $this->action = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::PUBLIC_BROADCAST]);
        $this->watchedPublicBroadcastStatus = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST]);
    }

    public function testPublicBroadcast(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::ALIEN_HOLOGRAPHIC_TV]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setEquipment($itemConfig);
        $I->haveInRepository($gameItem);

        /** @var CharacterConfig $player1Config */
        $player1Config = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHUN]);

        /** @var CharacterConfig $player2Config */
        $player2Config = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::DEREK]);

        /** @var Player $player1 */
        $player1 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player1->setPlayerVariables($player1Config);
        $player1
            ->setActionPoint(10)
            ->setMoralPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $player1Info = new PlayerInfo($player1, $user, $player1Config);
        $I->haveInRepository($player1Info);
        $player1->setPlayerInfo($player1Info);
        $I->refreshEntities($player1);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($player2Config);
        $player2
            ->setActionPoint(10)
            ->setMoralPoint(6);
        $player2Info = new PlayerInfo($player2, $user, $player2Config);
        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $this->publicBroadcastAction->loadParameters($this->action, $player1, $gameItem);

        $I->assertTrue($this->publicBroadcastAction->isVisible());
        $I->assertNull($this->publicBroadcastAction->cannotExecuteReason());

        $this->publicBroadcastAction->execute();

        $I->assertEquals(8, $player1->getActionPoint());
        $I->assertEquals(9, $player1->getMoralPoint());

        $I->assertEquals(10, $player2->getActionPoint());
        $I->assertEquals(9, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testPublicBroadcastAlreadyWatched(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ToolItemEnum::ALIEN_HOLOGRAPHIC_TV]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setEquipment($itemConfig);
        $I->haveInRepository($gameItem);

        /** @var CharacterConfig $player1Config */
        $player1Config = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::CHUN]);

        /** @var CharacterConfig $player2Config */
        $player2Config = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::DEREK]);

        /** @var Player $player1 */
        $player1 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player1->setPlayerVariables($player1Config);
        $player1
            ->setActionPoint(10)
            ->setMoralPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $player1Info = new PlayerInfo($player1, $user, $player1Config);
        $I->haveInRepository($player1Info);
        $player1->setPlayerInfo($player1Info);
        $I->refreshEntities($player1);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($player2Config);
        $player2
            ->setActionPoint(10)
            ->setMoralPoint(6);
        $player2Info = new PlayerInfo($player2, $user, $player2Config);
        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $this->publicBroadcastAction->loadParameters($this->action, $player1, $gameItem);

        $I->assertTrue($this->publicBroadcastAction->isVisible());
        $I->assertNull($this->publicBroadcastAction->cannotExecuteReason());

        $this->publicBroadcastAction->execute();
        $this->publicBroadcastAction->execute();

        $I->assertEquals(6, $player1->getActionPoint());
        $I->assertEquals(9, $player1->getMoralPoint());

        $I->assertEquals(10, $player2->getActionPoint());
        $I->assertEquals(9, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::PUBLIC_BROADCAST,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
