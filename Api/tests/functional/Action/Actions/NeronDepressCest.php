<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\NeronDepress;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class NeronDepressCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private NeronDepress $neronDepress;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::NERON_DEPRESS->value]);
        $this->neronDepress = $I->grabService(NeronDepress::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::NERON_DEPRESSION, $I);
        $this->kuanTi->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));
    }

    public function shouldPrintSecretLog(FunctionalTester $I): void
    {
        $this->givenPlayerDepressesNeron();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: **Chun** a l'air en grande discussion avec... NERON. Mais que peuvent-ils bien se dire ces deux-là ?",
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::NERON_DEPRESS_SUCCESS,
                visibility: VisibilityEnum::SECRET,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldChangeCpuPriorityAtCycleChange(FunctionalTester $I): void
    {
        $this->givenCpuPriorityIsOnNone();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenCpuPriorityShouldNotBeNone($I);
    }

    public function shouldChangeCrewLockAtCycleChange(FunctionalTester $I): void
    {
        $this->givenCrewLockIsOnProjects();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenCrewLockShouldNotBeProjects($I);
    }

    public function shouldChangeFoodDestructionOptionAtCycleChange(FunctionalTester $I): void
    {
        $this->givenFoodDestructionOptionIsOnNever();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenFoodDestructionOptionShouldNotBeOnNever($I);
    }

    public function shouldChangeVocodedAnnouncementsAtCycleChange(FunctionalTester $I): void
    {
        $this->givenVocodedAnnouncementsIsOn();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenVocodedAnnouncementsShouldBeOff($I);
    }

    public function shouldChangeDeathAnnouncementsAtCycleChange(FunctionalTester $I): void
    {
        $this->givenDeathAnnouncementsIsOn();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenDeathAnnouncementsShouldBeOff($I);
    }

    public function shouldTogglePlasmaShieldAtCycleChange(FunctionalTester $I): void
    {
        $this->givenPlasmaShieldIsOn($I);

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenPlasmaShieldShouldBeOff($I);
    }

    public function shouldToggleMagneticNetAtCycleChange(FunctionalTester $I): void
    {
        $this->givenMagneticNetIsOn($I);

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenMagneticNetShouldBeOff($I);
    }

    public function shouldChangeInhibitionAtCycleChangeIfItIsDeactivated(FunctionalTester $I): void
    {
        $this->givenNeronIsNotInhibited();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenNeronIsInhibited($I);
    }

    public function shouldNotChangeInhibitionAtCycleChangeIfItIsAlreadyInhibited(FunctionalTester $I): void
    {
        $this->givenNeronIsInhibited();

        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $this->thenNeronIsInhibited($I);
    }

    public function shouldCreateNeronAnnouncementAtCycleChange(FunctionalTester $I): void
    {
        $this->givenPlayerDepressesNeron();

        $this->whenCyclePasses();

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::NERON_DEPRESSION,
            ]
        );
    }

    public function changeInhibitionMessageShouldBeCycleChangeDate(FunctionalTester $I): void
    {
        $neron = $this->daedalus->getNeron();
        $neron->toggleInhibition();

        $this->givenPlayerDepressesNeron();

        $cycleChangeDate = new \DateTime()->modify('-2 minutes');
        $this->whenCyclePasses($cycleChangeDate);

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::NERON_DEPRESSION,
                'createdAt' => $cycleChangeDate,
            ]
        );

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::ACTIVATE_DMZ,
                'createdAt' => $cycleChangeDate,
            ]
        );
    }

    private function givenCpuPriorityIsOnNone(): void
    {
        $this->daedalus->getNeron()->setCpuPriority(NeronCpuPriorityEnum::NONE);
    }

    private function givenCrewLockIsOnProjects(): void
    {
        $this->daedalus->getNeron()->changeCrewLockTo(NeronCrewLockEnum::PROJECTS);
    }

    private function givenNeronIsNotInhibited(): void
    {
        $this->daedalus->getNeron()->setIsInhibited(false);
    }

    private function givenFoodDestructionOptionIsOnNever(): void
    {
        $this->daedalus->getNeron()->changeFoodDestructionOption(NeronFoodDestructionEnum::NEVER);
    }

    private function givenVocodedAnnouncementsIsOn(): void
    {
        if ($this->daedalus->getNeron()->areVocodedAnnouncementsActive() === false) {
            $this->daedalus->getNeron()->toggleVocodedAnnouncements();
        }
    }

    private function givenPlasmaShieldIsOn(FunctionalTester $I): void
    {
        $this->finishProject($this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD), $this->player, $I);

        if ($this->daedalus->getNeron()->isPlasmaShieldActive() === false) {
            $this->daedalus->getNeron()->togglePlasmaShield();
        }
    }

    private function givenMagneticNetIsOn(FunctionalTester $I): void
    {
        $this->finishProject($this->daedalus->getProjectByName(ProjectName::MAGNETIC_NET), $this->player, $I);

        if ($this->daedalus->getNeron()->isMagneticNetActive() === false) {
            $this->daedalus->getNeron()->toggleMagneticNet();
        }
    }

    private function givenDeathAnnouncementsIsOn(): void
    {
        if ($this->daedalus->getNeron()->areDeathAnnouncementsActive() === false) {
            $this->daedalus->getNeron()->toggleDeathAnnouncements();
        }
    }

    private function givenNeronIsInhibited(): void
    {
        $this->daedalus->getNeron()->setIsInhibited(true);
    }

    private function givenPlayerDepressesNeron(): void
    {
        $this->neronDepress->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->neronDepress->execute();
    }

    private function whenCyclePasses(\DateTime $cycleChangeDate = new \DateTime()): void
    {
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: $cycleChangeDate,
            ),
            name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
        );
    }

    private function thenCpuPriorityShouldNotBeNone(FunctionalTester $I): void
    {
        $I->assertNotEquals(NeronCpuPriorityEnum::NONE, $this->daedalus->getNeron()->getCpuPriority());
    }

    private function thenCrewLockShouldNotBeProjects(FunctionalTester $I): void
    {
        $I->assertNotEquals(NeronCrewLockEnum::PROJECTS, $this->daedalus->getNeron()->getCrewLock());
    }

    private function thenNeronIsInhibited(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->getNeron()->isInhibited());
    }

    private function thenFoodDestructionOptionShouldNotBeOnNever(FunctionalTester $I): void
    {
        $I->assertNotEquals(NeronFoodDestructionEnum::NEVER, $this->daedalus->getNeron()->getFoodDestructionOption());
    }

    private function thenVocodedAnnouncementsShouldBeOff(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getNeron()->areVocodedAnnouncementsActive());
    }

    private function thenDeathAnnouncementsShouldBeOff(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getNeron()->areDeathAnnouncementsActive());
    }

    private function thenPlasmaShieldShouldBeOff(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getNeron()->isPlasmaShieldActive());
    }

    private function thenMagneticNetShouldBeOff(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getNeron()->isMagneticNetActive());
    }
}
