<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Repository\InMemoryLinkWithSolRepository;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
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
        $this->linkWithSolRepository = new InMemoryLinkWithSolRepository();
        $this->createLinkWithSolForDaedalusService = new CreateLinkWithSolForDaedalusService(
            $this->linkWithSolRepository
        );
    }

    public function testShouldCreateLinkWithSolForDaedalus(): void
    {
        $this->createLinkWithSolForDaedalusService->execute(daedalusId: 1);

        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow(1);

        self::assertEquals(1, $linkWithSol->getDaedalusId());
        self::assertEquals(0, $linkWithSol->getStrength());
        self::assertFalse($linkWithSol->isEstablished());
    }
}
