<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Communications\Service\CreateTransportTradeService;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;

final readonly class CreateHunterService
{
    public function __construct(
        private CreateTransportTradeService $createTransportTrade,
        private DaedalusRepositoryInterface $daedalusRepository,
        private HunterRepositoryInterface $hunterRepository,
    ) {}

    public function execute(string $hunterName, int $daedalusId, \DateTime $time = new \DateTime()): void
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($daedalusId);

        $hunter = new Hunter(
            hunterConfig: $daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow($hunterName),
            daedalus: $daedalus,
        );
        $this->hunterRepository->save($hunter);

        if ($hunterName === HunterEnum::TRANSPORT) {
            $this->createTransportTrade->execute($hunter->getId(), $time);
        }
    }
}
