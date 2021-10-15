<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ReportEquipment;
use Mush\Action\Actions\ReportFire;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

class EquipmentSubscriberCest
{
    private ReportFire $reportFire;
    private ReportEquipment $reportEquipment;

    public function _before(FunctionalTester $I)
    {
        $this->reportFire = $I->grabService(ReportFire::class);
        $this->reportEquipment = $I->grabService(ReportEquipment::class);
    }

    public function testReportEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

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
            ->setName(ActionEnum::REPORT_EQUIPMENT)
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

        $status = new Status($gameEquipment);
        $status
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gameEquipment);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertBroken);

        $this->reportEquipment->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->reportEquipment->isVisible());

        $this->reportEquipment->execute();

        $I->SeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->SeeInRepository(AlertElement::class, ['place' => $room, 'equipment' => $gameEquipment, 'player' => $player]);
        $I->SeeInRepository(Message::class, [
            'author' => null,
            'neron' => $neron,
            'message' => NeronMessageEnum::REPORT_EQUIPMENT,
        ]);
    }

    public function testReportFire(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::REPORT_EQUIPMENT)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig->setActions(new ArrayCollection([$action]));
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $status = new Status($room);
        $status
            ->setName(StatusEnum::FIRE)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $reportedAlert = new AlertElement();
        $reportedAlert->setPlace($room);
        $I->haveInRepository($reportedAlert);

        $alertFire = new Alert();
        $alertFire
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::FIRES)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertFire);

        $this->reportFire->loadParameters($action, $player);

        $I->assertTrue($this->reportFire->isVisible());

        $this->reportFire->execute();

        $I->SeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::FIRES]);
        $I->SeeInRepository(AlertElement::class, ['place' => $room, 'player' => $player]);
        $I->SeeInRepository(Message::class, [
            'author' => null,
            'neron' => $neron,
            'message' => NeronMessageEnum::REPORT_FIRE,
        ]);
    }
}
