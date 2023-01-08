<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
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
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class AutomaticGetUpCest
{
    private Shower $showerAction;

    public function _before(FunctionalTester $I)
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testAutomaticGetUp(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->setStatusConfigs(new ArrayCollection([$statusConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $getUpAction = new Action();
        $getUpAction
            ->setActionName(ActionEnum::GET_UP)
            ->setScope(ActionScopeEnum::SELF)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($getUpAction);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$getUpAction])]);
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

        $action = new Action();
        $action
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment($room);

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
        ;
        $I->haveInRepository($gameEquipment);

        $this->showerAction->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
