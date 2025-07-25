<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function shouldDecreaseOxygen(FunctionalTester $I): void
    {
        // given Daedalus has 10 oxygen
        $this->daedalus->setOxygen(10);

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Daedalus has 7 oxygen (-1 base + 2*-1 per missing / broken oxygen tank)
        $I->assertEquals(7, $this->daedalus->getOxygen());
    }

    public function testOxygenBreakOnCycleChange(FunctionalTester $I)
    {
        // add a lot of incident points so that oxygen breaks
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $this->daedalus->addIncidentPoints(500);
        $this->daedalus->setOxygen(10);

        $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::OXYGEN_TANK,
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
            [],
            new \DateTime()
        );

        $this->whenANewCyclePasses();

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
        $this->whenANewCyclePasses();

        // then Jin Su is dead and is not commander, but Gioele is commander
        $I->assertFalse($jinSu->isAlive());
        $I->assertEmpty($jinSu->getTitles());
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);
    }

    public function shouldNotAssignTitleToInactivePlayer(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Jin Su is inactive
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $jinSu,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Jin Su is not commander, but Gioele is commander
        $I->assertEmpty($jinSu->getTitles());
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);

        // then title holders list should show only Gioele ever being a commander
        $I->assertEquals($this->daedalus->getDaedalusInfo()->getTitleHolders(TitleEnum::COMMANDER), ['gioele']);
    }

    public function shouldGiveBackTitleToExInactivePlayers(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Gioele is commander
        $gioele->addTitle(TitleEnum::COMMANDER);
        $this->daedalus->getDaedalusInfo()->addTitleHolder(TitleEnum::COMMANDER, $gioele->getLogName());
        $I->haveInRepository($gioele);

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Jin Su is commander, but Gioele is not commander anymore
        $I->assertTrue($jinSu->hasTitle(TitleEnum::COMMANDER));
        $I->assertEmpty($gioele->getTitles());

        // then title holders list should show Gioele being the first commander then Jin Su
        $I->assertEquals($this->daedalus->getDaedalusInfo()->getTitleHolders(TitleEnum::COMMANDER), ['gioele', 'jin_su']);
    }

    public function shouldNotGiveTitlesToHighlyInactivePlayers(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Jin Su is highly inactive
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $jinSu,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Jin Su is not commander, but Gioele is commander
        $I->assertEmpty($jinSu->getTitles());
        $I->assertEquals($gioele->getTitles(), [TitleEnum::COMMANDER]);

        // then title holders list should show only Gioele
        $I->assertEquals($this->daedalus->getDaedalusInfo()->getTitleHolders(TitleEnum::COMMANDER), ['gioele']);
    }

    public function shouldRemoveTitlesFromInactivePlayers(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $gioele */
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        // given Jin Su is commander
        $jinSu->addTitle(TitleEnum::COMMANDER);
        $this->daedalus->getDaedalusInfo()->addTitleHolder(TitleEnum::COMMANDER, $jinSu->getLogName());
        $I->haveInRepository($jinSu);

        // given Jin Su is inactive
        (new \ReflectionProperty($jinSu, 'lastActionDate'))->setValue($jinSu, new \DateTime('-3 days'));
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $jinSu,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Jin Su is not commander anymore
        $I->assertFalse($jinSu->hasTitle(TitleEnum::COMMANDER));

        // then title holders list should show Jin Su being the first commander then Gioele
        $I->assertEquals($this->daedalus->getDaedalusInfo()->getTitleHolders(TitleEnum::COMMANDER), ['jin_su', 'gioele']);
    }

    public function shouldRecordTitleHolders(FunctionalTester $I): void
    {
        // given daedalus is in game so titles can be assigned
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

        // given those players in the daedalus
        /** @var Player $jinSu */
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        /** @var Player $janice */
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);

        /** @var Player $eleesha */
        $eleesha = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ELEESHA);

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then titles should be assigned
        $I->assertTrue($jinSu->hasTitle(TitleEnum::COMMANDER));
        $I->assertTrue($janice->hasTitle(TitleEnum::NERON_MANAGER));
        $I->assertTrue($eleesha->hasTitle(TitleEnum::COM_MANAGER));

        // when Jin Su and Janice go inactive
        (new \ReflectionProperty($jinSu, 'lastActionDate'))->setValue($jinSu, new \DateTime('-1 days'));
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $jinSu,
            tags: [],
            time: new \DateTime()
        );
        (new \ReflectionProperty($janice, 'lastActionDate'))->setValue($janice, new \DateTime('-1 days'));
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $janice,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Kuan Ti is new commander
        $I->assertTrue($this->kuanTi->hasTitle(TitleEnum::COMMANDER));

        // then Eleesha is new NERON admin
        $I->assertTrue($eleesha->hasTitle(TitleEnum::NERON_MANAGER));

        // when Jin Su stops being inactive
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $jinSu,
            tags: [],
            time: new \DateTime()
        );

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then Jin Su is back at being the commander
        $I->assertTrue($jinSu->hasTitle(TitleEnum::COMMANDER));
        $I->assertFalse($this->kuanTi->hasTitle(TitleEnum::COMMANDER));

        // then titleHolderList has correct values
        $I->assertEquals($this->daedalus->getDaedalusInfo()->getTitleHolders(), [
            TitleEnum::COMMANDER => ['jin_su', 'kuan_ti'],
            TitleEnum::NERON_MANAGER => ['janice', 'eleesha'],
            TitleEnum::COM_MANAGER => ['eleesha'],
        ]);
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
        $this->whenANewCyclePasses();

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
        $this->whenANewCyclePasses();

        // then Daedalus has 50 shield
        $I->assertEquals(50, $this->daedalus->getShield());
    }

    public function shouldCreateANeronAnnouncementWhenAutoWateringRemovesFires(FunctionalTester $I): void
    {
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
        $this->whenANewCyclePasses();

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
        $this->whenANewCyclePasses();

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

        // given a lot of incident points
        $this->daedalus->addIncidentPoints(500);

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

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
        // given Daedalus is in game so incidents can happen
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);

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

        // when cycle change event is triggered
        $this->whenANewCyclePasses();

        // then I should see a neron announcement
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'message' => NeronMessageEnum::PATCHING_UP,
            ]
        );
    }

    public function shouldNeronKillWhenOnlyMushRemainOnCycleChange(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->whenAllHumanPlayersDie();
        $I->assertTrue($this->kuanTi->isAlive());
        $this->kuanTi->setTriumph(2);
        $this->whenANewCyclePasses();
        $I->assertFalse($this->kuanTi->isAlive());
        $I->assertEquals(EndCauseEnum::KILLED_BY_NERON, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getEndCause());
        $I->assertNotEquals(EndCauseEnum::KILLED_BY_NERON, $this->chun->getPlayerInfo()->getClosedPlayer()->getEndCause());
        $I->assertEquals(10, $this->kuanTi->getTriumph());
        $I->assertEquals(10, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(GameStatusEnum::FINISHED, $this->daedalus->getDaedalusInfo()->getGameStatus());
    }

    private function whenANewCyclePasses(): void
    {
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function whenAllHumanPlayersDie(): void
    {
        foreach ($this->daedalus->getAlivePlayers()->getHumanPlayer() as $human) {
            $this->playerService->killPlayer(
                player: $human,
                endReason: EndCauseEnum::DEPRESSION,
                time: new \DateTime(),
            );
        }
    }
}
