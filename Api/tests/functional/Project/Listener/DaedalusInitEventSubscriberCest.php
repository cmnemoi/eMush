<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\Entity\Project;
use Mush\Project\Listener\DaedalusInitEventSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusInitEventSubscriberCest extends AbstractFunctionalTest
{
    private DaedalusInitEventSubscriber $daedalusInitEventSubscriber;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusInitEventSubscriber = $I->grabService(DaedalusInitEventSubscriber::class);
    }

    public function testProjectsAreCreatedOnNewDaedalusEvent(FunctionalTester $I): void
    {
        // given I have a new daedalus event
        $daedalusInitEvent = new DaedalusInitEvent(
            daedalus: $this->daedalus,
            daedalusConfig: $this->daedalus->getDaedalusConfig(),
            tags: [],
            time: new \DateTime(),
        );

        // when I listen to the DaedalusInitEvent
        $this->daedalusInitEventSubscriber->onNewDaedalus($daedalusInitEvent);

        // then Daedalus should have all its projects created
        $expectedNumberOfProjects = \count(ProjectConfigData::getAll());
        $I->assertCount($expectedNumberOfProjects, $I->grabEntitiesFromRepository(Project::class));
    }

    public function testShouldProposeThreeNeronProjectsOnNewDaedalusEvent(FunctionalTester $I): void
    {
        // given I have a new daedalus event
        $daedalusInitEvent = new DaedalusInitEvent(
            daedalus: $this->daedalus,
            daedalusConfig: $this->daedalus->getDaedalusConfig(),
            tags: [],
            time: new \DateTime(),
        );

        // when I listen to the DaedalusInitEvent
        $this->daedalusInitEventSubscriber->onNewDaedalus($daedalusInitEvent);

        // then Daedalus should have all 3 Neron projects created
        $I->assertCount(expectedCount: 3, haystack: $this->daedalus->getProposedNeronProjects());
    }
}
