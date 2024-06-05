<?php

declare(strict_types=1);

namespace Mush\Daedalus\UseCase;

use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronCrewLockEnum;
use Mush\Daedalus\Repository\NeronRepositoryInterface;

final readonly class ChangeNeronCrewLockUseCase
{
    public function __construct(private NeronRepositoryInterface $neronRepository) {}

    public function execute(Neron $neron, NeronCrewLockEnum $newCrewLock): void
    {
        $neron->changeCrewLockTo($newCrewLock);
        $this->neronRepository->save($neron);
    }
}
