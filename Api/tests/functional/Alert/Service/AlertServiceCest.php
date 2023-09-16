<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Alert\Service;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class AlertServiceCest extends AbstractFunctionalTest
{
    private AlertServiceInterface $alertService;
    private GameEquipment $mycoscan;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $mycoscanConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => 'mycoscan']);
        $this->mycoscan = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $this->mycoscan->setEquipment($mycoscanConfig);
        $this->mycoscan->setName(EquipmentEnum::MYCOSCAN);
        $I->haveInRepository($this->mycoscan);

        $this->alertService = $I->grabService(AlertServiceInterface::class);
    }

    public function testHandleEquipmentBreakCreateAnAlertElement(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(Alert::class);
        // given a mycoscan

        // when handleEquipmentBreak is called on it
        $this->alertService->handleEquipmentBreak($this->mycoscan);

        // then check that an alert element is created
        /** @var Alert $alert */
        $alert = $I->grabEntityFromRepository(Alert::class);

        /** @var AlertElement $alertElement */
        $alertElement = $this->alertService->getAlertEquipmentElement($alert, $this->mycoscan);
        $I->assertNotNull($alertElement);
    }

    public function testHandleEquipmentBreakDoesNotCreateMultipleAlertElementForASingleEquipment(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(Alert::class);
        // given a mycoscan

        // when handleEquipmentBreak is called twice on it
        $this->alertService->handleEquipmentBreak($this->mycoscan);
        $this->alertService->handleEquipmentBreak($this->mycoscan);

        // then check that a single alert element is created
        /** @var Alert $alert */
        $alert = $I->grabEntityFromRepository(Alert::class);

        $alertElements = $alert->getAlertElements();
        $I->assertCount(1, $alertElements);
    }

    public function testGetAlertEquipmentElement(FunctionalTester $I): void
    {
        // given a mycoscan, break it and get the alert
        $this->alertService->handleEquipmentBreak($this->mycoscan);
        /** @var Alert $alert */
        $alert = $I->grabEntityFromRepository(Alert::class);

        // when getAlertEquipmentElement is called on it
        $alertElement = $this->alertService->getAlertEquipmentElement($alert, $this->mycoscan);

        // then check that the alert element is returned
        $I->assertNotNull($alertElement);
    }

    public function testGetAlertEquipmentWithoutAlertElementBefore(FunctionalTester $I): void
    {
        // given a mycoscan, break it and get the alert. remove alert element
        $this->alertService->handleEquipmentBreak($this->mycoscan);
        /** @var Alert $alert */
        $alert = $I->grabEntityFromRepository(Alert::class);

        $alertElements = $alert->getAlertElements();
        $alertElement = $alertElements->first();
        $alert->getAlertElements()->removeElement($alertElement);

        // when getAlertEquipmentElement is called on it
        $alertElement = $this->alertService->getAlertEquipmentElement($alert, $this->mycoscan);

        // then check that the alert element is returned
        $I->assertNotNull($alertElement);
    }

    public function testHandleEquipmentBreakDontCreateAlertForGameItem(FunctionalTester $I): void
    {
        // given a GameItem like a walkie talkie
        $walkieTalkieConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::WALKIE_TALKIE]);
        $walkieTalkie = new GameItem($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $walkieTalkie->setEquipment($walkieTalkieConfig);
        $walkieTalkie->setName(ItemEnum::WALKIE_TALKIE);
        $I->haveInRepository($walkieTalkie);

        // when handleEquipmentBreak is called on it
        $this->alertService->handleEquipmentBreak($walkieTalkie);

        // then check that no alert is created
        $I->dontSeeInRepository(Alert::class);
    }
}
