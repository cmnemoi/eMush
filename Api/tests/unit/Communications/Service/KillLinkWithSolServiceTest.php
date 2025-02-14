<?php

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Service\KillLinkWithSolService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
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

    public function testShouldMakeLinkWithSolUnestablished(): void
    {
        $linkWithSol = $this->givenLinkWithSolIsEstablished();

        $this->whenIKillLinkWithSol();

        $this->thenLinkWithSolShouldNotBeEstablished($linkWithSol);
    }

    private function givenLinkWithSolIsEstablished(): LinkWithSol
    {
        $linkWithSol = new LinkWithSol($this->daedalus->getId(), isEstablished: true);
        $this->linkWithSolRepository->save($linkWithSol);

        return $linkWithSol;
    }

    private function whenIKillLinkWithSol(): void
    {
        $eventService = $this->createStub(EventServiceInterface::class);
        $killLinkWithSolService = new KillLinkWithSolService(
            $eventService,
            $this->linkWithSolRepository
        );
        $killLinkWithSolService->execute($this->daedalus->getId());
    }

    private function thenLinkWithSolShouldNotBeEstablished(LinkWithSol $linkWithSol): void
    {
        self::assertFalse($linkWithSol->isEstablished(), 'LinkWithSol should be unestablished');
    }
}
