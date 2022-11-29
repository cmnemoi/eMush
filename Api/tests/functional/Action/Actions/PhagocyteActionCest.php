<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Phagocyte;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class PhagocyteActionCest
{
    private Phagocyte $phagocyteAction;

    public function _before(FunctionalTester $I)
    {
        $this->phagocyteAction = $I->grabService(Phagocyte::class);
    }

    public function testPhagocyteWithOneSpore(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $phagocyteActionEntity = new Action();
        $phagocyteActionEntity
            ->setName(ActionEnum::PHAGOCYTE)
            ->setScope(ActionScopeEnum::SELF)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $I->haveInRepository($phagocyteActionEntity);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig->setActions(new ArrayCollection([$phagocyteActionEntity]));

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 1,
            'healthPoint' => 1,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setGameConfig($gameConfig);
        $I->haveInRepository($mushConfig);

        $mushStatus = new Status($player, $mushConfig);
        $I->haveInRepository($mushStatus);

        $sporeStatusConfig = new ChargeStatusConfig();
        $sporeStatusConfig
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setGameConfig($gameConfig);
        $I->haveInRepository($sporeStatusConfig);

        $sporeStatus = new ChargeStatus($player, $sporeStatusConfig);
        $sporeStatus->setCharge(1);
        $I->haveInRepository($sporeStatus);

        $this->phagocyteAction->loadParameters($phagocyteActionEntity, $player);
        $this->phagocyteAction->execute();

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES);
        $I->assertEquals(0, $sporeStatus->getCharge());
        $I->assertEquals(5, $player->getActionPoint());
        $I->assertEquals(5, $player->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room,
            'playerInfo' => $player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::PHAGOCYTE_SUCCESS,
        ]);
    }
}
