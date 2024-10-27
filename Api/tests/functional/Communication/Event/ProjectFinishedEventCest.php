<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communication\Event;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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
        // given I have a project finished event for PILGRED
        $projectEvent = new ProjectEvent(
            project: $this->daedalus->getPilgred(),
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then I should see a Neron message telling me PILGRED has been reppaired
        $neronAnnouncement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::REPAIRED_PILGRED],
        );
        $I->assertEquals($neronAnnouncement->getTranslationParameters()['character'], 'chun');
    }

    public function shouldCreateMessageAtNeronProjectFinishedEvent(FunctionalTester $I): void
    {
        // given I have a project finished event for a neron project
        $projectEvent = new ProjectEvent(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then I should see a Neron message telling me neron project has been completed
        $neronAnnouncement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::NEW_PROJECT],
        );
    }

    public function shouldNotCreateMessageWhenResearchIsFinished(FunctionalTester $I): void
    {
        // given research project is finished
        $research = $this->daedalus->getProjectByName(ProjectName::RETRO_FUNGAL_SERUM);
        $research->finish();

        // given I have a project finished event for a research project
        $projectEvent = new ProjectEvent(
            project: $research,
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then I should not see a Neron message telling me research is finished
        $I->dontSeeInRepository(Message::class, [
            'message' => NeronMessageEnum::NEW_PROJECT,
        ]);
    }
}
