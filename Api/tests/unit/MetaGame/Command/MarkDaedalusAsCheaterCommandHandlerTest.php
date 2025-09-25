<?php

declare(strict_types=1);

namespace Mush\Tests\unit\MetaGame\Command;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\MetaGame\Command\MarkDaedalusAsCheaterCommand;
use Mush\MetaGame\Command\MarkDaedalusAsCheaterCommandHandler;
use Mush\Tests\unit\Daedalus\TestDoubles\InMemoryClosedDaedalusRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MarkDaedalusAsCheaterCommandHandlerTest extends TestCase
{
    private InMemoryClosedDaedalusRepository $closedDaedalusRepository;
    private ClosedDaedalus $closedDaedalus;

    public function testShouldMarkDaedalusAsCheater(): void
    {
        $this->givenAClosedDaedalus();

        $this->whenMarkingDaedalusAsCheater();

        $this->thenDaedalusShouldBeMarkedAsCheater();
    }

    private function givenAClosedDaedalus(): void
    {
        $this->closedDaedalusRepository = new InMemoryClosedDaedalusRepository();
        $this->closedDaedalus = DaedalusFactory::createDaedalus()->getDaedalusInfo()->getClosedDaedalus();
        $this->closedDaedalusRepository->save($this->closedDaedalus);
    }

    private function whenMarkingDaedalusAsCheater(): void
    {
        $command = new MarkDaedalusAsCheaterCommand(closedDaedalusId: $this->closedDaedalus->getId());
        $markDaedalusAsCheaterHandler = new MarkDaedalusAsCheaterCommandHandler($this->closedDaedalusRepository);
        $markDaedalusAsCheaterHandler->execute($command);
    }

    private function thenDaedalusShouldBeMarkedAsCheater(): void
    {
        $savedClosedDaedalus = $this->closedDaedalusRepository->findOneByIdOrThrow($this->closedDaedalus->getId());
        self::assertTrue($savedClosedDaedalus->isCheater(), 'Closed daedalus should be marked as cheater');
    }
}
