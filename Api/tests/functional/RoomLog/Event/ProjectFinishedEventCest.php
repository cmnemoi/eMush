<?php

declare(strict_types=1);

namespace Mush\Tests\functional\RoomLog\Event;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldCreateMessageAtPilgredFinishedEvent(FunctionalTester $I): void
    {
        // given I have a project finished event for a research project
        $projectEvent = new ProjectEvent(
            project: $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM),
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then I should see a public log telling me research has been completed
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "La Recherche a progressé. L'article **Serum Rétro-Fongique** a été publié et ses applications immédiates sont prêtes.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::RESEARCH_COMPLETED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }
}
