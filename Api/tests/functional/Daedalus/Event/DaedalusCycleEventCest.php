<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldDecreaseOxygen(FunctionalTester $I): void
    {
        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 7 oxygen (-1 base + 2*-1 per missing / broken oxygen tank)
        $I->assertEquals(7, $this->daedalus->getOxygen());
    }

    public function testOxygenBreakOnCycleChange(FunctionalTester $I)
    {
        // let's increase the duration of the ship to increase the number of incidents
        $this->daedalus
            ->setOxygen(10)
            ->setDay(100);

        $this->player->getVariableByName(PlayerVariableEnum::MORAL_POINT)->setMaxValue(200)->setValue(200);
        $this->player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->setMaxValue(200)->setValue(200);

        $tankConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::OXYGEN_TANK]);

        $tankEquipment = $tankConfig->createGameEquipment($this->player->getPlace());
        $I->haveInRepository($tankEquipment);

        $event = new EquipmentEvent(
            $tankEquipment,
            true,
            VisibilityEnum::PUBLIC,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $this->daedalus->getModifiers());

        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // we cannot be sure that the tank is broken, but chances are really high so overall the test works
        // base oxygen loss is -3 with one operational tank it should be -2
        $I->assertEquals(8, $this->daedalus->getOxygen());
    }

    public function testCycleSubscriberDoNotAssignTitleToDeadPlayer(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Jin Su has 0 morale points so he dies at cycle change
        $jinSu->setMoralPoint(0);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Jin Su is dead and is not commander, but Gioele is commander
        $I->assertFalse($jinSu->isAlive());
        $I->assertEmpty($jinSu->getTitles());
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
    }

    public function testSporesAreResetAtDayChange(FunctionalTester $I): void
    {
        // given Daedalus has 0 spores
        $this->daedalus->setSpores(0);

        // given Daedalus is at D1C8 so next cycle change is also a day change
        $this->daedalus->setDay(1);
        $this->daedalus->setCycle(8);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 4 spores
        $I->assertEquals(4, $this->daedalus->getSpores());
    }

    public function shouldImproveDaedalusShieldByFiveIfPlasmaShieldProjectIsActive(FunctionalTester $I): void
    {
        // given Plasma Shield project is finished and activated
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            $this->chun,
            $I
        );
        $this->daedalus->getNeron()->togglePlasmaShield();

        // given Daedalus has 50 shield
        $I->assertEquals(50, $this->daedalus->getShield());

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 55 shield
        $I->assertEquals(55, $this->daedalus->getShield());
    }

    public function shouldNotImproveDaedalusShieldByFiveIfPlasmaShieldIsDeactivated(FunctionalTester $I): void
    {
        // given Plasma Shield project is finished but not activated
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            $this->chun,
            $I
        );

        // given Daedalus has 50 shield
        $I->assertEquals(50, $this->daedalus->getShield());

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Daedalus has 50 shield
        $I->assertEquals(50, $this->daedalus->getShield());
    }

    public function shouldCreateANeronAnnouncementWhenAutoWateringRemovesFires(FunctionalTester $I): void
    {
        $this->setupNoIncidents();

        // given auto watering project is finished
        $autoWatering = $this->daedalus->getProjectByName(ProjectName::AUTO_WATERING);
        $this->finishProject(
            project: $autoWatering,
            author: $this->chun,
            I: $I
        );

        // given it has a 100% activation rate
        $autoWateringConfig = $autoWatering->getConfig();
        $reflection = new \ReflectionClass($autoWateringConfig);
        $reflection->getProperty('activationRate')->setValue($autoWateringConfig, 100);

        // given Chun's room is on fire
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // when a new cycle passes
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then a Neron announcement should have been created
        $announcement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::AUTOMATIC_SPRINKLERS,
            ]
        );

        // then the announcement should have the number of fires extinguished
        $I->assertEquals(1, $announcement->getTranslationParameters()['quantity']);
    }

    public function shouldNotCreateANeronAnnouncementWhenAutoWateringDoesNotRemoveFire(FunctionalTester $I): void
    {
        // given Daedalus is at Day 0 so no incidents are triggered
        $this->daedalus->setDay(0);

        // given auto watering project is finished
        $autoWatering = $this->daedalus->getProjectByName(ProjectName::AUTO_WATERING);
        $this->finishProject(
            project: $autoWatering,
            author: $this->chun,
            I: $I
        );

        // given it has a 100% activation rate
        $autoWateringConfig = $autoWatering->getConfig();
        $reflection = new \ReflectionClass($autoWateringConfig);
        $reflection->getProperty('activationRate')->setValue($autoWateringConfig, 100);

        // given Chun's room is on fire
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime()
        );

        // given a new cycle passes
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // given the fire is extinguished
        $I->assertFalse($this->chun->getPlace()->hasStatus(StatusEnum::FIRE));

        // when a new cycle passes
        $oneHourLater = (new \DateTime())->modify('+1 hour');
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            $oneHourLater
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then I should not have created a Neron announcement
        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::AUTOMATIC_SPRINKLERS,
                'createdAt' => $oneHourLater,
            ]
        );
    }

    public function shouldPreventAllIncidentsIfBricBrocProjectIsActivated(FunctionalTester $I): void
    {
        // given Bric Broc project is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::BRIC_BROC),
            $this->chun,
            $I
        );

        // given bric broc project activation rate is 100%
        $bricBroc = $this->daedalus->getProjectByName(ProjectName::BRIC_BROC);
        $config = $bricBroc->getConfig();
        (new \ReflectionClass($config))->getProperty('activationRate')->setValue($config, 100);

        // given Daedalus is Day 100 so a lot of incidents should happen
        $this->daedalus->setDay(100);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then I should see no incidents in logs

        // no panic crisis
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => PlayerModifierLogEnum::PANIC_CRISIS,
            ]
        );

        // nor fires
        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::NEW_FIRE,
            ]
        );

        // nor metal plates
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::METAL_PLATE,
            ]
        );

        // nor tremors
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => LogEnum::TREMOR_NO_GRAVITY,
            ]
        );
    }

    public function shouldCreateANeronAnnouncementWhenBricBrocIsActivated(FunctionalTester $I): void
    {
        // given Bric Broc project is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::BRIC_BROC),
            $this->chun,
            $I
        );

        // given bric broc project activation rate is 100%
        $bricBroc = $this->daedalus->getProjectByName(ProjectName::BRIC_BROC);
        $config = $bricBroc->getConfig();
        (new \ReflectionClass($config))->getProperty('activationRate')->setValue($config, 100);

        // given Daedalus is Day 100 so a lot of incidents should happen
        $this->daedalus->setDay(100);

        // when cycle change event is triggered
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then I should see a neron announcement
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::PATCHING_UP,
            ]
        );
    }

    private function setupNoIncidents(): void
    {
        $this->daedalus->setDay(0);
        $daedalusConfig = $this->daedalus->getDaedalusConfig();
        $ref = new \ReflectionClass($daedalusConfig);
        $ref->getProperty('cyclePerGameDay')->setValue($daedalusConfig, 1_000_000);
    }
}
