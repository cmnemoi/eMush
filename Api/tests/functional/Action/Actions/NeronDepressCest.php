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
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
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
            target: null,
        );
        $this->neronDepress->execute();
    }

    private function whenCyclePasses(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
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
}
