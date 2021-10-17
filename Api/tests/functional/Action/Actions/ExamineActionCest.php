<?php

namespace Mush\Tests\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Examine;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\VisibilityEnum;

class ExamineActionCest
{
    private Examine $examine;

    public function _before(FunctionalTester $I)
    {
        $this->examine = $I->grabService(Examine::class);
    }

    public function testReportEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::EXAMINE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::NARCOTIC_DISTILLER,
            'actions' => new ArrayCollection([$action]),
        ]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setEquipment($equipmentConfig)
            ->setPlace($room)
        ;
        $I->haveInRepository($gameEquipment);

        $this->examine->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->examine->isVisible());

        $this->examine->execute();

        $I->seeInRepository(RoomLog::class, [
            'place' => $room,
            'player' => $player,
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => EquipmentEnum::NARCOTIC_DISTILLER . '.examine',
            'type' => 'equipments',
        ]);
    }
}
