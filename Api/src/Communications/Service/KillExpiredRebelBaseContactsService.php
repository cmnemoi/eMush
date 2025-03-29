<?php

declare(strict_types=1);

namespace Mush\Communications\Service;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;

final readonly class KillExpiredRebelBaseContactsService
{
    public function __construct(
        private CycleServiceInterface $cycleService,
        private DaedalusRepositoryInterface $daedalusRepository,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {}

    public function execute(int $daedalusId, \DateTime $time): void
    {
        $contactingBases = $this->rebelBaseRepository->findAllContactingRebelBases($daedalusId);
        $daedalus = $this->daedalusRepository->findByIdOrThrow($daedalusId);

        foreach ($contactingBases as $rebelBase) {
            if ($this->rebelBaseContactExpired($rebelBase, $daedalus, $time)) {
                $this->terminateContact($rebelBase);
            }
        }
    }

    private function rebelBaseContactExpired(RebelBase $rebelBase, Daedalus $daedalus, \DateTime $time): bool
    {
        return $this->numberOfCyclesSinceContact($rebelBase, $daedalus, $time) >= $this->contactDurationThreshold($daedalus);
    }

    private function terminateContact(RebelBase $rebelBase): void
    {
        $rebelBase->endContact();
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function contactDurationThreshold(Daedalus $daedalus): int
    {
        return $daedalus->getChargeStatusByNameOrThrow(DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION)->getCharge();
    }

    private function numberOfCyclesSinceContact(RebelBase $rebelBase, Daedalus $daedalus, \DateTime $time): int
    {
        return $this->cycleService->getNumberOfCycleElapsed(
            start: $rebelBase->getContactStartDateOrThrow(),
            end: $time,
            daedalusInfo: $daedalus->getDaedalusInfo(),
        );
    }
}
