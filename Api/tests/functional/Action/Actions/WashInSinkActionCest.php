<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\WashInSink;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class WashInSinkActionCest
{
    /* @var WashInSink */
    private WashInSink $washInSinkAction;

    public function _before(FunctionalTester $I)
    {
        $this->washInSinkAction = $I->grabService(WashInSink::class);
    }

    public function testHumanWashInSink(FunctionalTester $I)
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
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 3,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(3);
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::WASH_IN_SINK)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($action);

        $alreadyWashedInTheSink = new ChargeStatusConfig();
        $alreadyWashedInTheSink
            ->setName(PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setChargeVisibility(VisibilityEnum::HIDDEN)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStartCharge(1)
            ->setAutoRemove(true)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($alreadyWashedInTheSink);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);
        $I->haveInRepository($equipmentConfig);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::KITCHEN)
            ->setHolder($room);
        $I->haveInRepository($gameEquipment);

        $this->washInSinkAction->loadParameters($action, $player, $gameEquipment);
        $this->washInSinkAction->execute();

        $I->assertEquals(0, $player->getActionPoint());
        $I->assertTrue($player->hasStatus(PlayerStatusEnum::ALREADY_WASHED_IN_THE_SINK));

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::WASH_IN_SINK_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        $I->assertEquals($this->washInSinkAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_WASHED_IN_SINK_TODAY);
    }

    // @TODO MORE TEST BUT I DON'T UNDERSTAND WHAT HAPPEN TO THE SOAP
}
