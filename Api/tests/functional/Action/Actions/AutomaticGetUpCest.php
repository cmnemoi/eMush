<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
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
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class AutomaticGetUpCest
{
    private Disassemble $disassembleAction;

    public function _before(FunctionalTester $I)
    {
        $this->disassembleAction = $I->grabService(Disassemble::class);
    }

    public function testAutomaticGetUp(FunctionalTester $I)
    {
        $getUpAction = new ActionConfig();
        $getUpAction
            ->setActionName(ActionEnum::GET_UP)
            ->setRange(ActionRangeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);
        $I->haveInRepository($getUpAction);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
            ->setActionConfigs([$getUpAction]);
        $I->haveInRepository($statusConfig);

        $dirtyConfig = new StatusConfig();
        $dirtyConfig->setStatusName(PlayerStatusEnum::DIRTY)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dirtyConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$statusConfig, $dirtyConfig]));
        $I->flushToDatabase();

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $player->setActionPoint(2)->setHealthPoint(6);
        $I->flushToDatabase($player);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $lyingDownStatus = new Status($player, $statusConfig);
        $I->haveInRepository($lyingDownStatus);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::DISASSEMBLE)
            ->setRange(ActionRangeEnum::SELF)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actionConfigs' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower');
        $I->haveInRepository($gameEquipment);

        $this->disassembleAction->loadParameters(
            actionConfig: $action,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertTrue($this->disassembleAction->isVisible());
        $I->assertNull($this->disassembleAction->cannotExecuteReason());

        $this->disassembleAction->execute();

        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
