<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryLinkWithSolRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateLinkWithSolForDaedalusServiceTest extends TestCase
{
    private InMemoryLinkWithSolRepository $linkWithSolRepository;
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalusService;

    protected function setUp(): void
    {
        $eventService = self::createStub(EventServiceInterface::class);
        $this->linkWithSolRepository = new InMemoryLinkWithSolRepository();
        $this->createLinkWithSolForDaedalusService = new CreateLinkWithSolForDaedalusService(
            $eventService,
            $this->linkWithSolRepository
        );
    }

    public function testShouldCreateLinkWithSolForDaedalus(): void
    {
        // when I create a link with Sol for Daedalus
        $this->createLinkWithSolForDaedalusService->execute(daedalusId: 1);

        // then a link with Sol should be properly created
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow(1);

        // with expected values
        self::assertEquals(1, $linkWithSol->getDaedalusId());
        self::assertEquals(0, $linkWithSol->getStrength());
        self::assertFalse($linkWithSol->isEstablished());
    }
}
