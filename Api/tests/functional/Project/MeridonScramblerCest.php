<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MeridonScramblerCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldMakeHunterAimOtherHunters(FunctionalTester $I): void
    {
        $this->givenAttackingHunters(2);

        $this->givenMeridonScramblerHas100PercentsActivationRate();

        $this->givenMeridonScramblerIsFinished($I);

        $this->whenCyclePasses();

        $this->thenHuntersShouldAimOtherHunter($I);
    }

    public function shouldNotMakeHunterAimOtherHuntersIfNotFinished(FunctionalTester $I): void
    {
        $this->givenAttackingHunters(2);

        $this->givenMeridonScramblerHas100PercentsActivationRate();

        $this->whenCyclePasses();

        $this->thenHuntersShouldNotAimAtOtherHunter($I);
    }

    private function givenAttackingHunters(int $numberOfHunters): void
    {
        $this->daedalus->setHunterPoints($numberOfHunters * 10);
        $this->eventService->callEvent(
            event: new HunterPoolEvent(
                daedalus: $this->daedalus,
                tags: [],
                time: new \DateTime(),
            ),
            name: HunterPoolEvent::UNPOOL_HUNTERS
        );
    }

    private function givenMeridonScramblerIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::MERIDON_SCRAMBLER),
            author: $this->chun,
            I: $I
        );
    }

    private function givenMeridonScramblerHas100PercentsActivationRate(): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::MERIDON_SCRAMBLER);
        $config = $project->getConfig();

        $reflection = new \ReflectionClass($config);
        $reflection->getProperty('activationRate')->setValue($config, 100);
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

    private function thenHuntersShouldAimOtherHunter(FunctionalTester $I): void
    {
        foreach ($this->daedalus->getAttackingHunters() as $hunter) {
            $I->assertEquals(expected: Hunter::class, actual: $hunter->getTargetEntityOrThrow()::class);
        }
    }

    private function thenHuntersShouldNotAimAtOtherHunter(FunctionalTester $I): void
    {
        foreach ($this->daedalus->getAttackingHunters() as $hunter) {
            $I->assertNotEquals(expected: Hunter::class, actual: $hunter->getTargetEntityOrThrow()::class);
        }
    }
}
