<?php

namespace Mush\Tests\functional\Alert\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ReportEquipment;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DuplicateFiresAlertCest extends AbstractFunctionalTest
{
    private ReportEquipment $reportAction;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->reportAction = $I->grabService(ReportEquipment::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRemoveFireAndAddItAgain(FunctionalTester $I)
    {
        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => StatusEnum::FIRE]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'otherRoom']);

        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $room,
            [ActionEnum::SABOTAGE->value],
            new \DateTime()
        );
        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $room2,
            [ActionEnum::SABOTAGE->value],
            new \DateTime()
        );

        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::FIRES]);
        $I->seeInRepository(AlertElement::class, ['place' => $room]);
        $I->seeInRepository(AlertElement::class, ['place' => $room2]);

        // Now extinguish only one of the fire
        $this->statusService->removeStatus(
            StatusEnum::FIRE,
            $room2,
            [ActionEnum::SABOTAGE->value],
            new \DateTime()
        );
        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::FIRES]);
        $I->seeInRepository(AlertElement::class, ['place' => $room]);
        $I->dontSeeInRepository(AlertElement::class, ['place' => $room2]);
    }

    public function testAddFireAndInRoomWithBrokenEquipment(FunctionalTester $I)
    {
        $reportAction = $action = new ActionConfig();
        $reportAction
            ->setActionName(ActionEnum::REPORT_EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(
            EquipmentConfig::class,
            [
                'name' => EquipmentEnum::GRAVITY_SIMULATOR,
                'actionConfigs' => new ArrayCollection([$reportAction])]
        );

        $room = $this->player->getPlace();

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setName(EquipmentEnum::BED)
            ->setEquipment($gravitySimulatorConfig);

        $I->haveInRepository($gameEquipment);

        // break the equipment
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            [ActionEnum::SABOTAGE->value],
            new \DateTime()
        );
        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->dontSeeInRepository(AlertElement::class, ['place' => $room]);
        $I->seeInRepository(AlertElement::class, ['equipment' => $gameEquipment]);

        // player report ActionConfig
        $this->reportAction->loadParameters(
            actionConfig: $reportAction,
            actionProvider: $gameEquipment,
            player: $this->player,
            target: $gameEquipment
        );
        $this->reportAction->execute();

        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->dontSeeInRepository(AlertElement::class, ['place' => $room, 'equipment' => null]);
        $I->seeInRepository(AlertElement::class, ['equipment' => $gameEquipment, 'place' => $room]);

        // now create a fire
        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $room,
            [ActionEnum::SABOTAGE->value],
            new \DateTime()
        );
        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->seeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::FIRES]);
        $I->seeInRepository(AlertElement::class, ['place' => $room, 'equipment' => null]);
        $I->seeInRepository(AlertElement::class, ['equipment' => $gameEquipment, 'place' => $room]);
    }
}
