<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ReportEquipment;
use Mush\Action\Actions\ReportFire;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ReportActionCest extends AbstractFunctionalTest
{
    private ReportFire $reportFireAction;
    private ActionConfig $reportFireConfig;
    private ReportEquipment $reportEquipmentAction;
    private ActionConfig $reportEquipementConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;

    private StatusServiceInterface $statusService;

    private DaedalusIncidentServiceInterface $daedalusIncidentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->reportFireAction = $I->grabService(ReportFire::class);
        $this->reportFireConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::REPORT_FIRE]);

        $this->reportEquipmentAction = $I->grabService(ReportEquipment::class);
        $this->reportEquipementConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::REPORT_EQUIPMENT]);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->daedalusIncidentService = $I->grabService(DaedalusIncidentServiceInterface::class);
    }

    public function testReportEquipment(FunctionalTester $I)
    {
        // given there is a distiller in the room
        $tank = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::FUEL_TANK,
            $this->player->getPlace(),
            [],
            new \DateTime(),
        );

        // given the distiller is broken
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $tank,
            [],
            new \DateTime()
        );

        // when player report the broken distiller
        $this->reportEquipmentAction->loadParameters(
            actionConfig: $this->reportEquipementConfig,
            actionProvider: $tank,
            player: $this->player,
            target: $tank
        );

        $this->reportEquipmentAction->execute();

        // then there should be an alert and a message from neron
        $I->SeeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
        $I->SeeInRepository(AlertElement::class, ['place' => $this->player->getPlace(), 'equipment' => $tank, 'playerInfo' => $this->player->getPlayerInfo()]);
        $I->SeeInRepository(Message::class, [
            'author' => null,
            'neron' => $this->daedalus->getNeron(),
            'message' => NeronMessageEnum::REPORT_EQUIPMENT,
        ]);
    }

    public function testReportFire(FunctionalTester $I)
    {
        // given there is a fire  in the room
        $status = $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $this->player->getPlace(),
            [],
            new \DateTime()
        );
        // when player report the fire
        $this->reportFireAction->loadParameters(
            actionConfig: $this->reportFireConfig,
            actionProvider: $status,
            player: $this->player,
        );
        $this->reportFireAction->execute();

        // then there should be an alert and a message from neron
        $I->SeeInRepository(Alert::class, ['daedalus' => $this->daedalus, 'name' => AlertEnum::FIRES]);
        $I->SeeInRepository(AlertElement::class, ['place' => $this->player->getPlace(), 'playerInfo' => $this->player->getPlayerInfo()]);
        $I->SeeInRepository(Message::class, [
            'author' => null,
            'neron' => $this->daedalus->getNeron(),
            'message' => NeronMessageEnum::REPORT_FIRE,
        ]);
    }
}
