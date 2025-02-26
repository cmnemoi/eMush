<?php

namespace Mush\Tests\unit\Communications\Service;

use Mockery\MockInterface;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollService as D100Roll;
use Mush\Game\Service\Random\GetRandomIntegerService as RandomInteger;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryLinkWithSolRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class KillLinkWithSolServiceTest extends TestCase
{
    private Daedalus $daedalus;
    private InMemoryLinkWithSolRepository $linkWithSolRepository;

    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->linkWithSolRepository = new InMemoryLinkWithSolRepository();
    }

    public function testShouldMakeLinkWithSolUnestablishedOnSuccess(): void
    {
        $linkWithSol = $this->givenLinkWithSolIsEstablished();

        $this->whenISuccessfullyKillLinkWithSol();

        $this->thenLinkWithSolShouldNotBeEstablished($linkWithSol);
    }

    public function testShouldNotMakeLinkWithSolUnestablishedOnFailure(): void
    {
        $linkWithSol = $this->givenLinkWithSolIsEstablished();

        $this->whenIFailToKillLinkWithSol();

        $this->thenLinkWithSolShouldBeEstablished($linkWithSol);
    }

    public function testShouldNotKillAlreadyUnestablishedLinkWithSol(): void
    {
        $this->givenLinkWithSolIsNotEstablished();

        $spy = $this->whenISuccessfullyKillLinkWithSolWithSpy();

        $this->thenLinkWithSolEventShouldNotBeDispatched($spy);
    }

    private function givenLinkWithSolIsEstablished(): LinkWithSol
    {
        $linkWithSol = new LinkWithSol($this->daedalus->getId(), isEstablished: true);
        $this->linkWithSolRepository->save($linkWithSol);

        return $linkWithSol;
    }

    private function givenLinkWithSolIsNotEstablished(): LinkWithSol
    {
        $linkWithSol = new LinkWithSol($this->daedalus->getId(), isEstablished: false);
        $this->linkWithSolRepository->save($linkWithSol);

        return $linkWithSol;
    }

    private function whenISuccessfullyKillLinkWithSol(): void
    {
        $eventService = $this->createStub(EventServiceInterface::class);
        $killLinkWithSolService = new KillLinkWithSolService(
            new D100Roll(new RandomInteger()),
            $eventService,
            $this->linkWithSolRepository
        );
        $killLinkWithSolService->execute($this->daedalus->getId(), successRate: 100);
    }

    private function whenISuccessfullyKillLinkWithSolWithSpy(): MockInterface
    {
        $eventService = \Mockery::spy(EventServiceInterface::class);
        $killLinkWithSolService = new KillLinkWithSolService(
            new D100Roll(new RandomInteger()),
            $eventService,
            $this->linkWithSolRepository
        );
        $killLinkWithSolService->execute($this->daedalus->getId(), successRate: 100);

        return $eventService;
    }

    private function whenIFailToKillLinkWithSol(): void
    {
        $eventService = $this->createStub(EventServiceInterface::class);
        $killLinkWithSolService = new KillLinkWithSolService(
            new D100Roll(new RandomInteger()),
            $eventService,
            $this->linkWithSolRepository
        );
        $killLinkWithSolService->execute($this->daedalus->getId(), successRate: 0);
    }

    private function thenLinkWithSolShouldNotBeEstablished(LinkWithSol $linkWithSol): void
    {
        self::assertFalse($linkWithSol->isEstablished(), 'LinkWithSol should be unestablished');
    }

    private function thenLinkWithSolShouldBeEstablished(LinkWithSol $linkWithSol): void
    {
        self::assertTrue($linkWithSol->isEstablished(), 'LinkWithSol should be established');
    }

    private function thenLinkWithSolEventShouldNotBeDispatched(MockInterface $eventService): void
    {
        $eventService->shouldNotHaveReceived('callEvent');
    }
}
