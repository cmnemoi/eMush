<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\Service;

use Mush\Communications\Entity\NeronVersion;
use Mush\Communications\Service\UpdateNeronVersionService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Tests\unit\Communications\TestDoubles\Repository\InMemoryNeronVersionRepository;
use Mush\Tests\unit\Communications\TestDoubles\Service\FixedNeronMinorVersionIncrementService as FixedNeronMinorVersionIncrement;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UpdateNeronVersionServiceTest extends TestCase
{
    private InMemoryNeronVersionRepository $neronVersionRepository;
    private Daedalus $daedalus;
    private UpdateNeronVersionService $updateNeronVersion;

    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->neronVersionRepository = new InMemoryNeronVersionRepository();
    }

    public function testShouldThrowWhenGivenNegativeMinorIncrement(): void
    {
        $this->givenNeronVersionIs(1, 50);
        $this->givenMinorVersionIncrementIs(-1);

        $this->expectException(\InvalidArgumentException::class);

        $this->whenUpdatingNeronVersion();

        $this->thenNeronVersionShouldBe('1.50');
    }

    public function testShouldIncrementMajorWhenMinorExceeds100(): void
    {
        $this->givenNeronVersionIs(1, 99);
        $this->givenMinorVersionIncrementIs(10);

        $this->whenUpdatingNeronVersion();

        $this->thenNeronVersionShouldBe('2.00');
    }

    public function testShouldNotIncrementMajorIfMinorDoesNotReach100(): void
    {
        $this->givenNeronVersionIs(1, 98);
        $this->givenMinorVersionIncrementIs(1);

        $this->whenUpdatingNeronVersion();

        $this->thenNeronVersionShouldBe('1.99');
    }

    public function testShouldIncrementMajorWhenMinorReaches100(): void
    {
        $this->givenNeronVersionIs(1, 99);
        $this->givenMinorVersionIncrementIs(1);

        $this->whenUpdatingNeronVersion();

        $this->thenNeronVersionShouldBe('2.00');
    }

    private function givenNeronVersionIs(int $major, int $minor): void
    {
        $neronVersion = new NeronVersion($this->daedalus->getId(), $major, $minor);
        $this->neronVersionRepository->save($neronVersion);
    }

    private function givenMinorVersionIncrementIs(int $increment): void
    {
        $eventService = self::createStub(EventServiceInterface::class);
        $this->updateNeronVersion = new UpdateNeronVersionService(
            $eventService,
            new FixedNeronMinorVersionIncrement($increment),
            $this->neronVersionRepository,
        );
    }

    private function whenUpdatingNeronVersion(): void
    {
        $this->updateNeronVersion->execute($this->daedalus->getId());
    }

    private function thenNeronVersionShouldBe(string $expectedVersion): void
    {
        $neronVersion = $this->neronVersionRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        self::assertEquals($expectedVersion, $neronVersion->toString(), "NERON version should be {$expectedVersion}, but got {$neronVersion->toString()}");
    }
}
