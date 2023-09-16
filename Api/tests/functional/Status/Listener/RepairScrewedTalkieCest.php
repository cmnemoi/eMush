<?php

namespace Mush\Tests\functional\Status\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class RepairScrewedTalkieCest
{
    private Repair $repairAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
    }

    public function testRepairTalkie(FunctionalTester $I)
    {
        $brokenStatusConfig = new StatusConfig();
        $brokenStatusConfig->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($brokenStatusConfig);

        $screwedStatusConfig = new StatusConfig();
        $screwedStatusConfig->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($screwedStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$brokenStatusConfig, $screwedStatusConfig]),
            'localizationConfig' => $localizationConfig,
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $action = new Action();
        $action
            ->setActionName(ActionEnum::REPAIR)
            ->setActionCost(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'isBreakable' => true,
            'equipmentName' => ItemEnum::WALKIE_TALKIE,
            'name' => 'talkie_test',
        ]);

        $equipmentConfig->setActions(new ArrayCollection([$action]));

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setOwner($player2)
        ;
        $I->haveInRepository($gameEquipment);

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

        $status = new Status($gameEquipment, $brokenStatusConfig);
        $I->haveInRepository($status);

        $screwedStatus = new Status($player, $screwedStatusConfig);
        $screwedStatus->setTarget($player2);
        $I->haveInRepository($player2);

        $this->repairAction->loadParameters($action, $player2, $gameEquipment);

        $this->repairAction->execute();

        $I->assertFalse($player2->hasStatus(PlayerStatusEnum::TALKIE_SCREWED));
        $I->assertFalse($gameEquipment->hasStatus(EquipmentStatusEnum::BROKEN));
    }
}
