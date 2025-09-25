<?php

declare(strict_types=1);

namespace Mush\MetaGame\Command;

use Mush\Daedalus\Repository\ClosedDaedalusRepositoryInterface;

final readonly class MarkDaedalusAsCheaterCommandHandler
{
    public function __construct(
        private ClosedDaedalusRepositoryInterface $closedDaedalusRepository,
    ) {}

    public function execute(MarkDaedalusAsCheaterCommand $command): void
    {
        $closedDaedalus = $this->closedDaedalusRepository->findOneByIdOrThrow($command->closedDaedalusId);
        $closedDaedalus->markAsCheater();
        $this->closedDaedalusRepository->save($closedDaedalus);
    }
}
