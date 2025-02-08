<?php

namespace Mush\Communications\Service;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;

final readonly class CreateLinkWithSolForDaedalusService
{
    public function __construct(private LinkWithSolRepository $linkWithSolRepository) {}

    public function execute(int $daedalusId): void
    {
        $this->linkWithSolRepository->save(new LinkWithSol($daedalusId));
    }
}
