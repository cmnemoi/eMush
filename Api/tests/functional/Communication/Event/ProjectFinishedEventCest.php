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
            project: $this->createProject(ProjectName::PILGRED, $I),
            author: $this->chun,
        );

        // when I dispatch the event
        $this->eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);

        // then I should see a Neron message telling me PILGRED has been reppaired
        $I->seeInRepository(
            entity: Message::class,
            params: ['message' => NeronMessageEnum::REPAIRED_PILGRED],
        );
    }
}
