<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\TryKube;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\User\Entity\User;

class TryKubeCest
{
    private TryKube $tryKube;

    public function _before(FunctionalTester $I)
    {
        $this->tryKube = $I->grabService(TryKube::class);
    }

    public function testTryKube(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::TRY_KUBE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class);

        $itemConfig
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setActions(new ArrayCollection([$action]))
        ;

        $gameItem = new GameItem();
        $gameItem
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setEquipment($itemConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameItem);

        $this->tryKube->loadParameters($action, $player, $gameItem);

        $I->assertTrue($this->tryKube->isVisible());
        $I->assertNull($this->tryKube->cannotExecuteReason());

        $this->tryKube->execute();

        $I->assertEquals(9, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TRY_KUBE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
