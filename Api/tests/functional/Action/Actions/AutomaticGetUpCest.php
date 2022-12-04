<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\ActionOutputEnum;
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
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $getUpCost = new ActionCost();
        $getUpAction = new Action();
        $getUpAction
            ->setName(ActionEnum::GET_UP)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::SELF)
            ->setInjuryRate(0)
            ->setActionCost($getUpCost)
        ;
        $I->haveInRepository($getUpCost);
        $I->haveInRepository($getUpAction);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$getUpAction])]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
                                            'place' => $room,
                                            'actionPoint' => 2,
                                            'healthPoint' => 6,
                                        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $lyingDownStatus = new Status($player, $statusConfig);
        $I->haveInRepository($lyingDownStatus);

        $actionCost = new ActionCost();

        $action = new Action();
        $action
            ->setName(ActionEnum::SHOWER)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;
        $I->haveInRepository($actionCost);
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
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
