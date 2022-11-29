<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class ForceGetUpCest
{
    private Hit $hitAction;

    public function _before(FunctionalTester $I)
    {
        $this->hitAction = $I->grabService(Hit::class);
    }

    public function testForceGetUp(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'gameStatus' => GameStatusEnum::CURRENT]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();

        $action = new Action();
        $action
            ->setName(ActionEnum::HIT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$action])]);
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

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['actions' => new ArrayCollection([$action])]);
        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
            'characterConfig' => $characterConfig,
        ]);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig2);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $lyingDownStatus = new Status($player, $statusConfig);
        $I->haveInRepository($lyingDownStatus);

        $this->hitAction->loadParameters($action, $player2, $player);

        $I->assertTrue($this->hitAction->isVisible());
        $I->assertNull($this->hitAction->cannotExecuteReason());

        $this->hitAction->execute();

        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::FORCE_GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
