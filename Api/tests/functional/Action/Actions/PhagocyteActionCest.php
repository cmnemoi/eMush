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
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

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

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 1,
            'healthPoint' => 1,
            'characterConfig' => $characterConfig,
        ]);

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

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $phagocyteActionEntity = new Action();
        $phagocyteActionEntity
            ->setName(ActionEnum::PHAGOCYTE)
            ->setScope(ActionScopeEnum::SELF)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($phagocyteActionEntity);

        $characterConfig->setActions(new ArrayCollection([$phagocyteActionEntity]));
        $player->setCharacterConfig($characterConfig);

        $this->phagocyteAction->loadParameters($phagocyteActionEntity, $player);
        $this->phagocyteAction->execute();

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES);
        $I->assertEquals(0, $sporeStatus->getCharge());
        $I->assertEquals(5, $player->getActionPoint());
        $I->assertEquals(5, $player->getHealthPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room,
            'player' => $player,
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::PHAGOCYTE_SUCCESS,
        ]);
    }
}
